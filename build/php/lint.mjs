/**
 * build/php/lint.mjs — pure-JS PHP syntax check (php-parser) over every PHP
 * file in the deployable tree. This is our fast local lint; CI additionally
 * runs a real `php -l` in a PHP container.
 */
import phpParser from 'php-parser';
import { readFileSync } from 'node:fs';
import { execSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const repoRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

const parser = new phpParser({
  parser: { extractDoc: true, suppressErrors: false, version: 803 },
  ast: { withPositions: true },
});

const files = execSync(
  'find site/wp-content/themes/baaboul-houda site/wp-content/plugins/baaboul-houda-core site/wp-content/mu-plugins -name "*.php" -type f | sort',
  { cwd: repoRoot, encoding: 'utf8' },
)
  .trim()
  .split('\n')
  .filter(Boolean);

let failures = 0;
for (const file of files) {
  const code = readFileSync(path.join(repoRoot, file), 'utf8');
  try {
    parser.parseCode(code, file);
  } catch (e) {
    failures += 1;
    console.error(`✗ ${file}\n    ${String(e.message).split('\n').slice(0, 4).join('\n    ')}`);
  }
}
if (failures) {
  console.error(`${failures}/${files.length} PHP file(s) failed to parse`);
  process.exit(1);
}
console.log(`PHP parse OK — ${files.length} files`);
