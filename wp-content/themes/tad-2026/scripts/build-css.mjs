import fs from "node:fs/promises"; import path from "node:path";
import postcss from "postcss"; import cssnano from "cssnano"; import postcssImport from "postcss-import"; import postcssPresetEnv from "postcss-preset-env";
const root = path.resolve(new URL("..", import.meta.url).pathname);
const plugins=[postcssImport(),postcssPresetEnv({stage:2,features:{"nesting-rules":true,"color-mix":true}}),cssnano({preset:"default"})];
for (const f of ["main.css","editor.css"]) {
  const src=path.join(root,"assets/src/css",f), out=path.join(root,"assets/dist/css",f);
  const r=await postcss(plugins).process(await fs.readFile(src,"utf8"),{from:src,to:out,map:{inline:false}});
  await fs.writeFile(out,r.css); await fs.writeFile(out+".map",r.map.toString()); console.log("built",f,r.css.length);
}
