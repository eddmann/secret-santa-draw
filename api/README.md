# API

RESTful HAL-based API which backs the [Client](../client/).

## Usage

```
Usage:
  make <target>

Setup
  start                 Start the application in development mode
  restart               Restart the application in development mode
  stop                  Stop the application and clean up
  composer              Install the latest Composer dependencies

Testing/Linting
  can-release           Run all the same checks as CI to ensure this code will be releasable
  security              Check dependencies for known vulnerabilities
  test                  Run the test suite
  lint                  Run the linting tools
  format                Fix style related code violations

Running Instance
  open                  Open the API in the default browser
  shell                 Access a shell on the running container
  logs                  Tail the container logs
  ps                    List the running containers

Build/Deploy
  build                 Build application for deployment
  deploy                Deploy application via CDK
```

## Deployment

```sh
cp cdk/lib/environment.ts.example cdk/lib/environment.ts
export AWS_ACCESS_KEY_ID=""
export AWS_SECRET_ACCESS_KEY=""
make deploy
```
