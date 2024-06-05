package main

import (
	"fmt"
	"io/ioutil"
	"os"
)

func main() {

	f, err := os.Create("database.txt")
	if err != nil {
		panic(err)
	}

	f.WriteString("hello world2")
	f.Close()

	stream, err := ioutil.ReadFile("database.txt")
	if err != nil {
		panic(err)
	}

	fmt.Print(string(stream))
}
