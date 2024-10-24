package main

import (
	"bufio"
	"log"
	//	"encoding/xml"
	"fmt"
	"io"
	"os"
	"regexp"
	"strconv"
	"strings"
)

type PHPUnitConfig struct {
	DefaultTestSuite        string `xml:"defaultTestSuite,attr"`
	RequireCoverageMetadata bool   `xml:"requireCoverageMetadata,attr"`
	FailOnSkipped           bool   `xml:"failOnSkipped,attr"`
	TestSuites              []struct {
		Name string `xml:"name,attr"`
		Dir  string `xml:"directory"`
	} `xml:"testsuites>testsuite"`
	Coverage struct {
		PathCoverage bool `xml:"pathCoverage,attr"`
		Report       struct {
			Clover struct {
				File string `xml:"outputFile,attr"`
			} `xml:"clover"`
		} `xml:"report"`
	} `xml:"coverage"`
}

func main() {
	f, err := os.Open(`./var/coverage.txt`)
	if err != nil {
		log.Fatal(err)
	}
	defer f.Close()
	r := bufio.NewReader(f)
	thresholds := map[string]float64{"Paths": 75.0, "Branches": 75.0, "Lines": 75.0}
	rx := regexp.MustCompile(`\s([0-9]+\.[0-9]+)%\s+`)
	for {
		if line, err := r.ReadString('\n'); err == nil {
			line = strings.TrimSpace(line)
			if line == "" {
				continue
			}
			for k, v0 := range thresholds {
				if strings.HasPrefix(line, k+":") {
					m := rx.FindStringSubmatch(line)
					fmt.Println(m, len(m))
					if len(m) == 0 {
						continue
					}
					if v, err := strconv.ParseFloat(m[1], 64); err == nil {
						fmt.Println(line)
						if v < v0 {
							fmt.Println("too low:", k, v, " < ", v0)
						}
					}
					delete(thresholds, k)
					break
				}
			}
		} else if err == io.EOF {
			break
		} else {
			log.Fatal(err)
		}
	}
	fmt.Println(thresholds)
	/*
		configFile, err := os.Open("./phpunit.xml.dist")
		if err != nil {
			os.Stderr.WriteString(err.Error())
			os.Exit(1)
		}
		defer configFile.Close()
		decoder := xml.NewDecoder(configFile)
		var config PHPUnitConfig*/
	// for {
	// 	token, err := decoder.Token()
	// 	if err == io.EOF {
	// 		break
	// 	} else if err != nil {
	// 		fmt.Println("Error decoding XML file:", err)
	// 		os.Exit(1)
	// 	}
	// 	fmt.Printf("%T", token)
	// 	fmt.Println(token)
	// }
	/*
		err = decoder.Decode(&config)
		if err != nil {
			os.Stderr.WriteString(fmt.Sprintf("Error decoding XML file: %s", err))
			os.Exit(1)
		}
		fmt.Println(config.Coverage.Report.Clover)*/
	// var b []byte = make([]byte, 1024)
	// configFile.Read(b)
	// fmt.Println(b)
	// os.Stdout.Write(b)
}
