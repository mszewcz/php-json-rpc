# mszewcz/php-json-rpc
PHP 7.1+ implementation of JSON RPC 2.0. Client and server libraries can handle **regular requests**, **notifications**. **Batch requests** are supported.
Server supports **namespaces** and **self-description** (automatically provides input and output schema for each method in each namespace - please refer to [Wiki][wiki]). 
Client is able to send requests using **stream context**, **cURL** extension or by user defined transport class.

[![Build Status](https://travis-ci.com/mszewcz/php-json-rpc.svg?token=SKHyUu7D9k2gxfy5aKpX&branch=develop)](https://travis-ci.com/mszewcz/php-json-rpc)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/7863f6da48e748a5bac5dde5ba0e5608)](https://www.codacy.com?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mszewcz/php-json-rpc&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/7863f6da48e748a5bac5dde5ba0e5608)](https://www.codacy.com?utm_source=github.com&utm_medium=referral&utm_content=mszewcz/php-json-rpc&utm_campaign=Badge_Coverage)

## Contents
* [What is JSON RPC?](#JsonRpc)
* [Installation](#Installation)
* [Usage](#Usage)
* [Contributing](#Contributing)
* [License](#License)


<a name="JsonRpc"></a>
## What is JSON RPC?
JSON RPC is a simple and light weight communication protocol that allows clients and servers talk to each other. Version 2.0, which is implemented by this library, is described [here][json-spec]. Please take a while to read this, to understand request and response formats as well as some server-regarded rules.


<a name="Installation"></a>
## Installation
If you use [Composer][composer] to manage the dependencies simply add a dependency on ```mszewcz/php-json-rpc``` to your project's composer.json file. Here is a minimal example of a composer.json:
```
{
    "require": {
        "mszewcz/php-json-rpc": ">=1.0"
    }
}
```
You can also clone or download this respository.

**php-json-rpc** meets [PSR-4][psr4] autoloading standards. If using the Composer please include its autoloader file:
```php
require_once 'vendor/autoload.php';
```
If you cloned or downloaded this repository, you will have to code your own PSR-4 style autoloader implementation.

<a name="Usage"></a>
## Usage
Please refer to project [Wiki][wiki]:
- [Client usage][client]
- [Server usage][server]

You may also want to check ```examples``` directory.


<a name="Contributing"></a>
## Contributing
Contributions are welcome. Please send your contributions through GitHub pull requests 

Pull requests for bug fixes must be based on latest stable release from the ```master``` branch whereas pull requests for new features must be based on the ```developer``` branch.

Due to time constraints, I am not always able to respond as quickly as I would like. If you feel you're waiting too long for merging your pull request please remind me here.

#### Coding standards
I follow [PSR-2][psr2] coding style and [PSR-4][psr4] autoloading standards. Be sure you're also following them before sending us your pull request.


<a name="License"></a>
## License
**php-json-rpc** is licensed under the MIT License - see the ```LICENSE``` file for details.

[json-spec]:http://www.jsonrpc.org/specification
[composer]:http://getcomposer.org/
[wiki]:https://github.com/mszewcz/php-json-rpc/wiki
[client]:https://github.com/mszewcz/php-json-rpc/wiki/Client-usage
[server]:https://github.com/mszewcz/php-json-rpc/wiki/Server-usage
[psr2]:http://www.php-fig.org/psr/psr-2/
[psr4]:http://www.php-fig.org/psr/psr-4/
