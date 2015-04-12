Git S3
=============

> Upload your git repo to AWS S3

[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](http://www.opensource.org/licenses/MIT)
[![Latest Version](http://img.shields.io/packagist/v/pulkitjalan/git-s3.svg?style=flat-square)](https://packagist.org/packages/pulkitjalan/git-s3)
[![Total Downloads](https://img.shields.io/packagist/dt/pulkitjalan/git-s3.svg?style=flat-square)](https://packagist.org/packages/pulkitjalan/git-s3)

## Installation

A global installation of Composer is needed. __git-s3 is installed globally.__

```sh
$ composer global require pulkitjalan/git-s3:dev-master
```

## Usage

After the installation run `git-s3 init` to initialize the config files. All config files will be created in `~/.git-s3`. Once initialized you can run `git-s3 edit` to manually edit them.

There is also an optional `--env` optional parameter that can be used to initialize and upload to separate environments. Example:

```sh
$ git-s3 init --env development
```

Once ready you can push an application to s3. Make sure the command is run from a valid `git` repository. Example:

```sh
$ git-s3 push
```

If you want to push it as a zip archive then you can use the `--zip` parameter (useful for AWS CodeDeploy). Example:

```sh
$ git-s3 push --zip
```

## Credits

This package was inspired [schickling/git-s3](https://github.com/schickling/git-s3).
