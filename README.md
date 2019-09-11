# Checking server response http-codes
## Annotation
If you want to automatically check the http-codes of the site pages given by the remote server, this console application will help you.
Perhaps it will be useful for SEO control of the site (like me). It allows you to control any http-codes, but most likely you may need only 200, 301, 302 and 404.
## Quick start
- clone repository
```bash
$ git clone git@github.com:pmikle/checkHttpCodes.git
```
- install composer dependencies
```bash
$ composer install
```
 - run application
 ```bash
$ bin/console checkCodes 200
```
## Arguments
### code
Expected http-code. This is required parameter.  
### urls
File with checked urls.
This isn`t required parameter.
Default value defined in **config.php** (const **DEFAULT_FILE** or *urls.txt*).
### method
Using http-method.
This isn`t required parameter.
Default value defined in **config.php** (const **DEFAULT_HTTP_METHOD** or *GET*).
### verbose
Available verbose mode *-v*.
## Example
```bash
$ php bin\console checkCodes 204 urls.txt POST -v 
```
result
```bash
ok: https://google.com, current: 204, expected: 204
ok: https://google.com?1, current: 204, expected: 204
ok: https://google.com?2, current: 204, expected: 204
spend time: 2.3681349754333
all is well
```
## Use for autotests
If you want to use this script in your automatic testing, you can easily find out the result by requesting the code to complete the last operation:
```bash
$ $?
```
 - **true** or **1** - there are problems
 - **false** or **0** - successful
## Configs
### DEFAULT_FILE
Default file with testing urls.
### DEFAULT_HTTP_METHOD
Default http-method for request. Now available *GET* and *POST*.
### CHUNK_SIZE
The number of requests in one chunk.
To improve performance, it is possible to synchronously execute requests with chunks of equal sizes. Please note that increasing the size of the chunk may lead to brokers of the remote server. The recommended value is 10 requests in one chunk.