#!/usr/bin/env node
import "source-map-support/register";
import * as cdk from "aws-cdk-lib";
import { SecretSantaClientStack } from "../lib/stack";

const app = new cdk.App();

new SecretSantaClientStack(app, "SecretSantaClientStack", {
  env: {
    region: "eu-west-1",
  },
});
