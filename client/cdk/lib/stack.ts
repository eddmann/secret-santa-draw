import { Construct } from "constructs";
import { CfnOutput, Duration, Stack, StackProps } from "aws-cdk-lib";
import {
  AllowedMethods,
  CachePolicy,
  Distribution,
  OriginAccessIdentity,
  OriginRequestPolicy,
  ViewerProtocolPolicy,
} from "aws-cdk-lib/aws-cloudfront";
import { HttpOrigin, S3BucketOrigin } from "aws-cdk-lib/aws-cloudfront-origins";
import { BucketDeployment, Source } from "aws-cdk-lib/aws-s3-deployment";
import { Bucket, BucketAccessControl } from "aws-cdk-lib/aws-s3";
import { Certificate } from "aws-cdk-lib/aws-certificatemanager";
import { CacheControl } from "aws-cdk-lib/aws-codepipeline-actions";

import { config } from "./config";

const CODE_PATH = "../app/dist";
const UNCACHED_ASSETS = ["index.html", "sw.js"];

export class SecretSantaClientStack extends Stack {
  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);

    const bucket = new Bucket(this, "Bucket", {
      accessControl: BucketAccessControl.PRIVATE,
    });

    new BucketDeployment(this, "CachedBucketDeployment", {
      destinationBucket: bucket,
      sources: [Source.asset(CODE_PATH, { exclude: [...UNCACHED_ASSETS, ".well-known/*"] })],
      cacheControl: [CacheControl.setPublic(), CacheControl.maxAge(Duration.days(365)), CacheControl.immutable()],
      prune: false,
    });

    new BucketDeployment(this, "UncachedBucketDeployment", {
      destinationBucket: bucket,
      sources: [Source.asset(CODE_PATH, { exclude: ["*", ...UNCACHED_ASSETS.map((asset) => `!${asset}`)] })],
      cacheControl: [CacheControl.noCache(), CacheControl.noStore(), CacheControl.mustRevalidate()],
      prune: false,
    });

    // need to ensure that the `apple-app-site-association` has an `application/json` content type.
    // as there is only a single file in .well-known at this time we deploy the entire directory with
    // this content type.
    new BucketDeployment(this, "WellKnownBucketDeployment", {
      destinationBucket: bucket,
      sources: [Source.asset(CODE_PATH, { exclude: ["*", "!.well-known/*"] })],
      cacheControl: [CacheControl.noCache(), CacheControl.noStore(), CacheControl.mustRevalidate()],
      contentType: "application/json",
      prune: false,
    });

    const originAccessIdentity = new OriginAccessIdentity(this, "DistributionOriginAccessIdentity");
    bucket.grantRead(originAccessIdentity);

    const distribution = new Distribution(this, "Distribution", {
      defaultRootObject: "index.html",
      errorResponses: [
        {
          httpStatus: 404,
          responseHttpStatus: 200,
          responsePagePath: "/index.html",
          ttl: Duration.minutes(10),
        },
      ],
      domainNames: [config.domainName],
      certificate: Certificate.fromCertificateArn(this, "DistributionCertificate", config.certificateArn),
      defaultBehavior: {
        origin: S3BucketOrigin.withOriginAccessIdentity(bucket, { originAccessIdentity }),
        cachePolicy: CachePolicy.CACHING_OPTIMIZED,
        viewerProtocolPolicy: ViewerProtocolPolicy.REDIRECT_TO_HTTPS,
        originRequestPolicy: OriginRequestPolicy.ALL_VIEWER_EXCEPT_HOST_HEADER,
        allowedMethods: AllowedMethods.ALLOW_GET_HEAD_OPTIONS,
      },
      additionalBehaviors: {
        "/api/*": {
          origin: new HttpOrigin(config.apiOriginDomainName),
          cachePolicy: CachePolicy.CACHING_DISABLED,
          viewerProtocolPolicy: ViewerProtocolPolicy.REDIRECT_TO_HTTPS,
          originRequestPolicy: OriginRequestPolicy.ALL_VIEWER_EXCEPT_HOST_HEADER,
          allowedMethods: AllowedMethods.ALLOW_ALL,
        },
      },
    });

    new CfnOutput(this, "DistributionUrl", { value: `https://${distribution.domainName}` });
    new CfnOutput(this, "PublicUrl", { value: `https://${config.domainName}` });
  }
}
