import browserSync from "browser-sync";
import chokidar from "chokidar";
import esbuild from "esbuild";
import fg from "fast-glob";
import fs from "node:fs/promises";
import path from "node:path";
import postcss from "postcss";
import cssnano from "cssnano";
import postcssImport from "postcss-import";
import postcssPresetEnv from "postcss-preset-env";
import { optimize } from "svgo";

const root = path.resolve(new URL("..", import.meta.url).pathname);
const watch = process.argv.includes("--watch");
const dist = path.join(root, "assets/dist");
const postcssPlugins = [
  postcssImport(),
  postcssPresetEnv({
    stage: 2,
    features: {
      "nesting-rules": true,
      "color-mix": true
    }
  }),
  cssnano({ preset: "default" })
];

async function readJson(file) {
  return JSON.parse(await fs.readFile(path.join(root, file), "utf8"));
}

async function ensureDist() {
  await fs.mkdir(path.join(dist, "css"), { recursive: true });
  await fs.mkdir(path.join(dist, "js"), { recursive: true });
  await fs.mkdir(path.join(dist, "svg"), { recursive: true });
}

async function buildCss(file, outFile) {
  const sourcePath = path.join(root, "assets/src/css", file);
  const result = await postcss(postcssPlugins).process(await fs.readFile(sourcePath, "utf8"), {
    from: sourcePath,
    to: path.join(dist, "css", outFile),
    map: { inline: false }
  });

  await fs.writeFile(path.join(dist, "css", outFile), result.css);
  if (result.map) {
    await fs.writeFile(path.join(dist, "css", `${outFile}.map`), result.map.toString());
  }
}

async function buildJs(entry, outFile) {
  await esbuild.build({
    entryPoints: [path.join(root, "assets/src/js", entry)],
    bundle: true,
    minify: true,
    sourcemap: true,
    target: ["es2019"],
    outfile: path.join(dist, "js", outFile)
  });
}

async function buildSvgSprite() {
  const files = await fg("assets/src/svg/originals/*.svg", { cwd: root });
  const symbols = [];

  for (const file of files) {
    const fullPath = path.join(root, file);
    const id = `icon-${path.basename(file, ".svg")}`;
    const optimized = optimize(await fs.readFile(fullPath, "utf8"), {
      multipass: true,
      plugins: [
        "preset-default",
        { name: "removeViewBox", active: false },
        { name: "removeDimensions", active: true }
      ]
    });
    const svg = optimized.data;
    const viewBox = svg.match(/viewBox="([^"]+)"/)?.[1] || "0 0 24 24";
    const inner = svg.replace(/<svg[^>]*>/, "").replace("</svg>", "");
    symbols.push(`<symbol id="${id}" viewBox="${viewBox}">${inner}</symbol>`);
  }

  await fs.writeFile(path.join(dist, "svg/icons.svg"), `<svg xmlns="http://www.w3.org/2000/svg">${symbols.join("")}</svg>`);
}

async function build() {
  await ensureDist();
  await Promise.all([
    buildCss("main.css", "main.css"),
    buildCss("editor.css", "editor.css"),
    buildJs("main.js", "main.js"),
    buildJs("editor.js", "editor.js"),
    buildSvgSprite()
  ]);
  console.log("Built tracyappsdesign 2026 assets.");
}

await build();

if (watch) {
  const config = await readJson("starter.config.json");
  const bs = browserSync.create();

  bs.init({
    proxy: config.localUrl || "http://tracyappsdesign.local",
    files: ["**/*.php", "theme.json", "acf-json/*.json"],
    notify: false,
    open: false
  });

  chokidar.watch(["assets/src/**/*.{css,js,svg}"], { cwd: root, ignoreInitial: true }).on("all", async () => {
    try {
      await build();
      bs.reload();
    } catch (error) {
      console.error(error);
    }
  });
}
