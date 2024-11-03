package main

import (
	"bytes"
	"fmt"
	"io"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

func Execute(output_buffer *bytes.Buffer, stack ...*exec.Cmd) (err error) {
	var error_buffer bytes.Buffer
	pipe_stack := make([]*io.PipeWriter, len(stack)-1)
	i := 0
	for ; i < len(stack)-1; i++ {
		stdin_pipe, stdout_pipe := io.Pipe()
		stack[i].Stdout = stdout_pipe
		stack[i].Stderr = &error_buffer
		stack[i+1].Stdin = stdin_pipe
		pipe_stack[i] = stdout_pipe
	}
	stack[i].Stdout = output_buffer
	stack[i].Stderr = &error_buffer

	if err := call(stack, pipe_stack); err != nil {
		log.Fatalln(string(error_buffer.Bytes()), err)
	}
	return err
}

func call(stack []*exec.Cmd, pipes []*io.PipeWriter) (err error) {
	if stack[0].Process == nil {
		if err = stack[0].Start(); err != nil {
			return err
		}
	}
	if len(stack) > 1 {
		if err = stack[1].Start(); err != nil {
			return err
		}
		defer func() {
			if err == nil {
				pipes[0].Close()
				err = call(stack[1:], pipes[1:])
			}
		}()
	}
	return stack[0].Wait()
}

func main() {
	patternsToIgnore := []string{".*", "Tests/*", "*/Tests/*", "*.dist", "*.dist.*"}
	check := func(name string) bool {
		for _, pattern := range patternsToIgnore {
			ok, err := filepath.Match(pattern, name)
			if err != nil {
				panic(err)
			}
			if ok {
				return true
			}
		}
		return false
	}
	//	"git archive --format=tar --worktree-attributes HEAD | tar -t"
	cmdGit := exec.Command("git", "archive", "--format=tar", "--worktree-attributes", "HEAD")
	// var out bytes.Buffer
	outGit := new(bytes.Buffer)
	cmdGit.Stdout = outGit
	cmdGit.Stderr = os.Stderr
	// cmdGit.Start()
	// cmdGit.Wait()
	cmdGit.Run()
	cmdTar := exec.Command("tar", "-t")
	cmdTar.Stdin = outGit
	outTar := new(bytes.Buffer)
	cmdTar.Stdout = outTar
	cmdTar.Stderr = os.Stderr
	// cmdTar.Stdout = os.Stdout
	cmdTar.Run()
	// outTar.ReadString('\n')
	exportedFiles := map[string]interface{}{}
	var filesToIgnore []string
	for {
		line, err := outTar.ReadString('\n')
		if err == io.EOF {
			break
		} else if err != nil {
			fmt.Printf("error reading file %s\n", err)
		}
		line = strings.TrimSpace(line)
		if check(line) {
			filesToIgnore = append(filesToIgnore, line)
		} else {
			exportedFiles[line] = struct{}{}
		}
	}
	cmdGit = exec.Command("git", "ls-files")
	outGit = new(bytes.Buffer)
	cmdGit.Stdout = outGit
	cmdGit.Run()
	for {
		line, err := outGit.ReadString('\n')
		if err == io.EOF {
			break
		} else if err != nil {
			fmt.Printf("error reading file %s\n", err)
		}
		line = strings.TrimSpace(line)
		if _, ok := exportedFiles[line]; !ok {
			fmt.Println(line)
		}
	}
	// output, err := cmdGit.Output()
	// if err != nil {
	//     fmt.Println(string(output), err)
	//     os.Exit(1)
	// }
	// output.
	if len(filesToIgnore) > 0 {
		os.Stdout.WriteString("\nFiles to ignore:\n")
		for _, name := range filesToIgnore {
			os.Stdout.WriteString(name + "\n")
		}
		os.Exit(1)
	}
	//fmt.Println(string(output), err)
	// var b bytes.Buffer
	// if err := Execute(&b,
	//     exec.Command("git", "archive", "--format=tar", "--worktree-attributes", "HEAD"),
	//     exec.Command("tar", "-t"),
	// ); err != nil {
	//     log.Fatalln(err)
	// }
	//io.Copy(os.Stdout, out)
}
