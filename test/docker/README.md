Docker Images
-------------

This docker images should be used with PHPStorm or when you want
to run a command in a specific php version.

To access the shel inside the container:

```bash
docker run --rm -v $(pwd):/opt/project -w /opt/project -it php54 bash
```

## Composer 

To run composer in php old version is advisable to get the
correct constrains in packages and still be able to use
a more recent PHP version.
For example (based on php 5.4 docker-composer):

```bash
docker run --rm -v $(pwd):/opt/project -w /opt/project php54 composer install
```

When running docker inside the container it will run as root,
so after installing you have to fix the files permissions:

```bash
sudo chown -R ${USER}.${USER} vendor composer.lock
```

Note when configuring *PHPStorm* the autoload path should be inside the container `/opt/project/vendor/autoload.php`.
