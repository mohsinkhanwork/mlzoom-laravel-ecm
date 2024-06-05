<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;

class ExpenseReportController extends Controller
{
    protected Zone $zone;
    protected Provider $provider;
    protected Category $categories;
    protected Booking $booking;

    protected Account $account;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    protected BookingDetailsAmount $bookingDetailsAmount;

    public function __construct(Zone $zone, Provider $provider, Category $categories, Service $service, Booking $booking, Account $account, User $user, Transaction $transaction, BookingDetailsAmount $bookingDetailsAmount)
    {
        $this->zone = $zone;
        $this->provider = $provider;
        $this->categories = $categories;
        $this->booking = $booking;

        $this->service = $service;
        $this->account = $account;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->bookingDetailsAmount = $bookingDetailsAmount;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function getBusinessExpenseReport(Request $request)
    {
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
        ]);

        $zones = $this->zone->ofStatus(1)->select('id', 'name')->get();
        $categories = $this->categories->ofType('main')->select('id', 'name')->get();
        $sub_categories = $this->categories->ofType('sub')->select('id', 'name')->get();

        $search = $request['search'];
        $queryParams = ['search' => $search];
        if ($request->has('zone_ids')) {
            $queryParams['zone_ids'] = $request['zone_ids'];
        }
        if ($request->has('category_ids')) {
            $queryParams['category_ids'] = $request['category_ids'];
        }
        if ($request->has('sub_category_ids')) {
            $queryParams['sub_category_ids'] = $request['sub_category_ids'];
        }
        if ($request->has('date_range')) {
            $queryParams['date_range'] = $request['date_range'];
        }
        if ($request->has('date_range') && $request['date_range'] == 'custom_date') {
            $queryParams['from'] = $request['from'];
            $queryParams['to'] = $request['to'];
        }

        $date_range = $request['date_range'];
        if (is_null($date_range) || $date_range == 'all_time') {
            $deterministic = 'year';
        } elseif ($date_range == 'this_week' || $date_range == 'last_week') {
            $deterministic = 'week';
        } elseif ($date_range == 'this_month' || $date_range == 'last_month' || $date_range == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($date_range == 'this_year' || $date_range == 'last_year' || $date_range == 'last_6_month' || $date_range == 'this_year_1st_quarter' || $date_range == 'this_year_2nd_quarter' || $date_range == 'this_year_3rd_quarter' || $date_range == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif ($date_range == 'custom_date') {
            $from = Carbon::parse($request['from'])->startOfDay();
            $to = Carbon::parse($request['to'])->endOfDay();
            $diff = Carbon::parse($from)->diffInDays($to);

            if ($diff <= 7) {
                $deterministic = 'week';
            } elseif ($diff <= 30) {
                $deterministic = 'day';
            } elseif ($diff <= 365) {
                $deterministic = 'month';
            } else {
                $deterministic = 'year';
            }
        }
        $group_by_deterministic = $deterministic == 'week' ? 'day' : $deterministic;

        $filtered_booking_amounts = $this->bookingDetailsAmount
            ->with(['booking'])
            ->whereHas('booking', function ($query) use ($request) {
                self::filterQuery($query, $request)
                    ->ofBookingStatus('completed')
                    ->when($request->has('search'), function ($query) use ($request) {
                        $keys = explode(' ', $request['search']);
                        return $query->where(function ($query) use ($keys) {
                            foreach ($keys as $key) {
                                $query->where('readable_id', 'LIKE', '%' . $key . '%');
                            }
                        });
                    });
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        $amounts = $this->bookingDetailsAmount
            ->whereHas('booking', function ($query) use ($request) {
                self::filterQuery($query, $request)->ofBookingStatus('completed');
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(service_unit_cost) as service_unit_cost'),
                    DB::raw('sum(discount_by_admin) as discount_by_admin'),
                    DB::raw('sum(discount_by_provider) as discount_by_provider'),
                    DB::raw('sum(coupon_discount_by_admin) as coupon_discount_by_admin'),
                    DB::raw('sum(coupon_discount_by_provider) as coupon_discount_by_provider'),
                    DB::raw('sum(campaign_discount_by_admin) as campaign_discount_by_admin'),
                    DB::raw('sum(campaign_discount_by_provider) as campaign_discount_by_provider'),
                    DB::raw('sum(admin_commission) as admin_commission'),

                    DB::raw($group_by_deterministic . '(created_at) ' . $group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $bonus_amounts = $this->transaction
            ->where('trx_type', TRX_TYPE['add_fund_bonus'])
            ->when($request->has('date_range'), function ($query) use ($request) {
                self::timeFilterQuery($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(credit) as bonus'),
                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $all_expenses = [];
        foreach ($amounts as $key=>$amount) {
            foreach ($bonus_amounts as $bonus) {
                if ($amount[$group_by_deterministic] == data_get($bonus, $group_by_deterministic, null)) {
                    $all_expenses[$key] = array_merge($amount, $bonus);
                }
                if (!isset($all_expenses[$key])) {
                    $all_expenses[$key] = $amount;
                }
                if (!array_key_exists('bonus', $all_expenses[$key])) {
                    $all_expenses[$key]['bonus'] = 0;
                }
            }
        }

        $chart_data = ['normal_discount' => array(), 'campaign_discount' => array(), 'coupon_discount' => array(), 'bonus' => array(), 'expenses' => array(), 'timeline' => array()];
        $total_promotional_cost = ['total_expense' => 0, 'discount' => 0, 'coupon' => 0, 'campaign' => 0, 'bonus' => 0];
        if ($deterministic == 'month') {
            $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            foreach ($months as $month) {
                $found = 0;
                $chart_data['timeline'][] = $month;
                foreach ($all_expenses as $item) {
                    if ($item['month'] == $month) {
                        $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                        $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                        $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                        $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                        $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus']);
                        $found = 1;

                        $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                        $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                        $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                        $total_promotional_cost['total_expense'] += $total_promotional_cost['discount'] + $total_promotional_cost['coupon'] + $total_promotional_cost['campaign'] + $item['bonus'];
                        $total_promotional_cost['bonus'] += $item['bonus']??0;
                    }
                }

                if (!$found) {
                    $chart_data['normal_discount'][] = with_decimal_point(0);
                    $chart_data['campaign_discount'][] = with_decimal_point(0);
                    $chart_data['coupon_discount'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                    $chart_data['bonus'][] = with_decimal_point(0);
                }
            }

        } elseif ($deterministic == 'year') {
            foreach ($all_expenses as $item) {
                $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus']);
                $chart_data['timeline'][] = $item[$deterministic];

                $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                $total_promotional_cost['total_expense'] += $total_promotional_cost['discount'] + $total_promotional_cost['coupon'] + $total_promotional_cost['campaign'] + $item['bonus'];
                $total_promotional_cost['bonus'] += $item['bonus']??0;

            }
        } elseif ($deterministic == 'day') {
            if ($date_range == 'this_month') {
                $to = Carbon::now()->lastOfMonth();
            } elseif ($date_range == 'last_month') {
                $to = Carbon::now()->subMonth()->endOfMonth();
            } elseif ($date_range == 'last_15_days') {
                $to = Carbon::now();
            }

            $number = date('d', strtotime($to));

            for ($i = 1; $i <= $number; $i++) {
                $found = 0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses as $item) {
                    if ($item['day'] == $i) {
                        $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                        $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                        $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                        $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                        $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus']);
                        $found = 1;

                        $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                        $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                        $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                        $total_promotional_cost['total_expense'] += $total_promotional_cost['discount'] + $total_promotional_cost['coupon'] + $total_promotional_cost['campaign'] + $item['bonus'];
                        $total_promotional_cost['bonus'] += $item['bonus']??0;
                    }
                }
                if (!$found) {
                    $chart_data['normal_discount'][] = with_decimal_point(0);
                    $chart_data['campaign_discount'][] = with_decimal_point(0);
                    $chart_data['coupon_discount'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                    $chart_data['bonus'][] = with_decimal_point(0);
                }
            }
        } elseif ($deterministic == 'week') {
            if ($date_range == 'this_week') {
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
            } elseif ($date_range == 'last_week') {
                $from = Carbon::now()->subWeek()->startOfWeek();
                $to = Carbon::now()->subWeek()->endOfWeek();
            }

            for ($i = (int)$from->format('d'); $i <= (int)$to->format('d'); $i++) {
                $found = 0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses as $item) {
                    if ($item['day'] == $i) {
                        $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                        $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                        $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                        $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                        $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus']);
                        $found = 1;

                        $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                        $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                        $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                        $total_promotional_cost['total_expense'] += $total_promotional_cost['discount'] + $total_promotional_cost['coupon'] + $total_promotional_cost['campaign'] + $item['bonus'];
                        $total_promotional_cost['bonus'] += $item['bonus']??0;
                    }
                }
                if (!$found) {
                    $chart_data['normal_discount'][] = with_decimal_point(0);
                    $chart_data['campaign_discount'][] = with_decimal_point(0);
                    $chart_data['coupon_discount'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                    $chart_data['bonus'][] = with_decimal_point(0);
                }
            }
        }

        return view('adminmodule::admin.report.business.expense', compact('zones', 'categories', 'sub_categories', 'filtered_booking_amounts', 'chart_data', 'total_promotional_cost', 'deterministic', 'queryParams'));
    }

    public function getBusinessExpenseReportDownload(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
        ]);

        $filtered_booking_amounts = $this->bookingDetailsAmount
            ->with(['booking'])
            ->whereHas('booking', function ($query) use ($request) {
                self::filterQuery($query, $request)
                    ->ofBookingStatus('completed')
                    ->when($request->has('search'), function ($query) use ($request) {
                        $keys = explode(' ', $request['search']);
                        return $query->where(function ($query) use ($keys) {
                            foreach ($keys as $key) {
                                $query->where('readable_id', 'LIKE', '%' . $key . '%');
                            }
                        });
                    });
            })
            ->latest()
            ->get();

        return (new FastExcel($filtered_booking_amounts))->download(time() . '-business-expense-report.xlsx', function ($item) {
            return [
                'Booking ID' => $item->booking->readable_id ?? '',
                'Normal Discount (' . currency_symbol() . ')' => with_decimal_point($item['discount_by_admin']),
                'Coupon Discount (' . currency_symbol() . ')' => with_decimal_point($item['coupon_discount_by_admin']),
                'Campaign Discount (' . currency_symbol() . ')' => with_decimal_point($item['campaign_discount_by_admin']),
                'Total Expense (' . currency_symbol() . ')' => with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin']),
            ];
        });
    }

    /**
     * @param $instance
     * @param $request
     * @return mixed
     */
    function filterQuery($instance, $request): mixed
    {
        return $instance
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })
            ->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })
            ->when($request->has('date_range') && $request['date_range'] == 'custom_date', function ($query) use ($request) {
                $query->whereBetween('created_at', [Carbon::parse($request['from'])->startOfDay(), Carbon::parse($request['to'])->endOfDay()]);
            })
            ->when($request->has('date_range') && $request['date_range'] != 'custom_date', function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            });
    }

    function timeFilterQuery($instance, $request): mixed
    {
        return $instance
            ->when($request->has('date_range') && $request['date_range'] != 'custom_date', function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            });
    }
}
