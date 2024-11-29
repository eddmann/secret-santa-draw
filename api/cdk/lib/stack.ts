import { Construct } from "constructs";
import { CfnOutput, Duration, Stack, StackProps } from "aws-cdk-lib";
import { FunctionUrlAuthType } from "aws-cdk-lib/aws-lambda";
import { ConsoleFunction, packagePhpCode, PhpFpmFunction, PhpFunction } from "@bref.sh/constructs";
import { Queue } from "aws-cdk-lib/aws-sqs";
import { SqsEventSource } from "aws-cdk-lib/aws-lambda-event-sources";
import { Effect, PolicyStatement } from "aws-cdk-lib/aws-iam";

import { environment as additionalEnvironment } from "./environment";

const PHP_VERSION = "8.4";
const CODE_PATH = "../app";

export class SecretSantaApiStack extends Stack {
  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);

    const workerQueue = new Queue(this, "WorkerQueue", {
      queueName: `${id}-WorkerQueue`,
      visibilityTimeout: Duration.minutes(6),
      deadLetterQueue: {
        maxReceiveCount: 5,
        queue: new Queue(this, "WorkerDql", {
          queueName: `${id}-WorkerDql`,
          retentionPeriod: Duration.days(14),
        }),
      },
    });

    const workerQueueAccessPolicy = new PolicyStatement({
      effect: Effect.ALLOW,
      resources: [workerQueue.queueArn],
      actions: ["*"],
    });

    const environment = {
      APP_ENV: "production",
      APP_DEBUG: "false",

      QUEUE_CONNECTION: "sqs",
      SQS_QUEUE: workerQueue.queueUrl,

      SESSION_DRIVER: "database",
      SESSION_LIFETIME: "120",
      SESSION_ENCRYPT: "false",
      SESSION_PATH: "/",

      ...additionalEnvironment,
    };

    const webFunction = new PhpFpmFunction(this, "WebFunction", {
      phpVersion: PHP_VERSION,
      code: packagePhpCode(CODE_PATH),
      handler: "public/index.php",
      timeout: Duration.seconds(28),
      environment,
    });
    webFunction.addToRolePolicy(workerQueueAccessPolicy);

    const webFunctionUrl = webFunction.addFunctionUrl({
      authType: FunctionUrlAuthType.NONE,
    });

    const consoleFunction = new ConsoleFunction(this, "ConsoleFunction", {
      phpVersion: PHP_VERSION,
      code: packagePhpCode(CODE_PATH),
      handler: "artisan",
      timeout: Duration.minutes(2),
      environment,
    });
    consoleFunction.addToRolePolicy(workerQueueAccessPolicy);

    const workerFunction = new PhpFunction(this, "WorkerFunction", {
      phpVersion: PHP_VERSION,
      code: packagePhpCode(CODE_PATH),
      handler: "Bref\\LaravelBridge\\Queue\\QueueHandler",
      timeout: Duration.minutes(1),
      environment,
    });
    workerFunction.addEventSource(new SqsEventSource(workerQueue, { batchSize: 1 }));
    workerFunction.addToRolePolicy(workerQueueAccessPolicy);

    new CfnOutput(this, "ApiUrl", { value: webFunctionUrl.url });
  }
}
