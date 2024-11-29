#!/usr/bin/env node
import "source-map-support/register";
import * as cdk from "aws-cdk-lib";
import { SecretSantaApiStack } from "../lib/stack";

const app = new cdk.App();

new SecretSantaApiStack(app, "SecretSantaApiStack", {
  env: {
    region: "eu-west-1",
  },
});
