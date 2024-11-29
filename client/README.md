# Client

React-based Client for the Secret Santa Draw.

## Usage

```
Usage:
  make <target>

Setup
  start                 Start the application in development mode
  install               Install the latest NPM dependencies

Testing/Linting
  lint                  Run the linting tools
  format                Fix style related code violations

Running Instance
  open                  Open the API in the default browser
  shell                 Access a shell of the development environment

Build/Deploy
  build                 Build application for deployment
  deploy                Deploy application via CDK
```

## Deployment

```sh
cp cdk/lib/config.ts.example cdk/lib/config.ts
export AWS_ACCESS_KEY_ID=""
export AWS_SECRET_ACCESS_KEY=""
make deploy
```
