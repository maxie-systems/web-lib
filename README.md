# Docker Image
The image is used to run tests.

Build an image:
```
docker build . -t web-lib-dev:latest
```

Run a new container in background:
```
docker run -di --name web-lib-dev -v .:/usr/src/app web-lib-dev
```

Enter the container:
```
docker exec -it web-lib-dev sh
```

Run the Composer inside the container once it's created:
```
composer install
```

From now on you can run all tests using this command inside the Container:
```
composer test
```

Or you can use a new container every time you want to run tests:
```
docker run -it --rm -v .:/usr/src/app web-lib-dev composer test
```

Run only unit tests:
```
composer utest
```

Check your code against the PSR-12 coding standard:
```
composer test-psr12 src/FileName.php
```

Fix your code:
```
composer fix-psr12 src/FileName.php
```
