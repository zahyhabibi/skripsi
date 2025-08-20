// scripts/predict.mjs
import { client } from "@gradio/client";

async function main() {
  try {
    const raw = process.argv[2] || "{}";
    const payload = JSON.parse(raw);

    // --- Normalisasi BASE URL ---
    let base = (process.env.HF_SPACE_URL || "").trim();
    if (!base) throw new Error("HF_SPACE_URL is not set");
    // buang path seperti /run, /predict, /queue, dsb.
    base = base.replace(/\/+(run|queue|predict).*$/i, "").replace(/\/+$/, "");

    const token = process.env.HF_TOKEN || undefined;
    const app = await client(base, { hf_token: token });

    if (payload.describe) {
      const api = await app.view_api();
      console.log(JSON.stringify({ ok: true, api }, null, 2));
      return;
    }

    const fn = payload.fn || "/predict";
    const data = Array.isArray(payload.data) ? payload.data : [];
    const result = await app.predict(fn, data);
    console.log(JSON.stringify({ ok: true, result }));
  } catch (err) {
    console.error(err?.stack || err?.message || String(err));
    console.log(JSON.stringify({ ok: false, error: err?.message || String(err) }));
    process.exit(1);
  }
}
main();
