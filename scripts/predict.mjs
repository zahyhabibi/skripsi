// scripts/predict.mjs
import { client } from "@gradio/client";
import dns from "node:dns";
dns.setDefaultResultOrder("ipv4first");

function baseUrl() {
  const raw = (process.env.HF_SPACE_URL || "").trim();
  if (!raw) throw new Error("HF_SPACE_URL is not set");
  // buang sisa path seperti /run, /queue, /predict di ujung & trailing slash
  return raw.replace(/\/+(run|queue|predict).*$/i, "").replace(/\/+$/, "");
}

async function main() {
  try {
    const raw = process.argv[2] || "{}";
    const payload = JSON.parse(raw);

    const base = baseUrl();
    const token = process.env.HF_TOKEN || undefined;

    // Optional debug: cek /config (harus 200)
    // try { const r = await fetch(base + "/config"); console.error("preflight", r.status); } catch(e){ console.error("preflight error:", e?.message); }

    const app = await client(base, { hf_token: token });

    if (payload.describe) {
      const api = await app.view_api();
      console.log(JSON.stringify({ ok: true, mode: "describe", api }, null, 2));
      return;
    }

    const fn = payload.fn || "/predict";
    const data = Array.isArray(payload.data) ? payload.data : [];

    const job = await app.submit(fn, data); // queue-friendly
    let finalData = null, statuses = [], logs = [];

    for await (const ev of job) {
      if (ev.type === "status") statuses.push(ev);
      if (ev.type === "log") logs.push(ev.log);
      if (ev.type === "data") finalData = ev.data;
    }

    const last = statuses.at(-1);
    if (last && last.stage === "error") {
      console.log(JSON.stringify({ ok:false, error:"Space returned error", status:last, logs }));
      process.exit(1);
    }

    const result = finalData ?? (await job.result());
    console.log(JSON.stringify({ ok:true, result, statuses, logs }));
  } catch (err) {
    const out = { ok:false, error: err?.message || String(err), name: err?.name };
    if (err?.stack) out.stack = err.stack;
    const c = err?.cause;
    if (c) out.cause = { code: c.code, errno: c.errno, syscall: c.syscall, hostname: c.hostname };
    console.log(JSON.stringify(out));
    process.exit(1);
  }
}
main();
