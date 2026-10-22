import { IAspect, Stack } from "aws-cdk-lib";
import { CfnFunction } from "aws-cdk-lib/aws-lambda";
import { IConstruct } from "constructs";

// Bref v2 layers patched for the PHP October 2026 security release (CVE-2026-109618, CVE-2026-109619).
// @bref.sh/layers v2 has not been republished with them, so pin the eu-west-1 versions from bref/bref 2.4.22.
// Remove once @bref.sh/layers ships these versions (or newer).
const PATCHED_LAYER_VERSIONS: Record<string, number> = {
  "php-84": 43,
  "php-84-fpm": 43,
  "arm-php-84": 42,
  "arm-php-84-fpm": 42,
  console: 123,
};

const BREF_LAYER_ARN = /^(arn:aws:lambda:eu-west-1:534081306603:layer:)([a-z0-9-]+):(\d+)$/;

export class PatchedBrefLayers implements IAspect {
  visit(node: IConstruct): void {
    if (!(node instanceof CfnFunction) || !node.layers) return;

    const layers: string[] = Stack.of(node).resolve(node.layers);
    const patched = layers.map((arn) => {
      const match = BREF_LAYER_ARN.exec(arn);
      const version = match && PATCHED_LAYER_VERSIONS[match[2]];
      return version && version > Number(match[3]) ? `${match[1]}${match[2]}:${version}` : arn;
    });

    node.addPropertyOverride("Layers", patched);
  }
}
