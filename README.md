Git S3
=============

> Upload your git repo to AWS S3

[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](http://www.opensource.org/licenses/MIT)

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

Once ready you can upload an application to s3. Make sure the command is run from a valid `git` repository. Example:

```sh
$ git-s3 upload
```

If you want to upload it as a zip archive then you can use the `--zip` parameter. Useful for AWS CodeDeploy. Example:

```sh
$ git-s3 upload --zip
```
