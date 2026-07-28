#!/usr/bin/env python3
"""Regenerate docs/API.md from docs/openapi.yaml (single source of truth).
Usage:  python3 docs/gen_api_md.py
"""
import yaml, os
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
d = yaml.safe_load(open(os.path.join(ROOT, "docs/openapi.yaml")))
S = d["components"]["schemas"]; P = d["components"]["parameters"]

def refname(r): return r.split("/")[-1]

def typestr(s):
    if s is None: return "any"
    if "$ref" in s: return refname(s["$ref"])
    if "allOf" in s:
        for part in s["allOf"]:
            props = part.get("properties", {})
            if "data" in props: return typestr(props["data"])
        return "object"
    if "anyOf" in s:
        return "|".join(typestr(x) for x in s["anyOf"] if x.get("type") != "null") + "?"
    t = s.get("type")
    if isinstance(t, list):
        base = [x for x in t if x != "null"]; opt = "?" if "null" in t else ""
        core = base[0] if base else "any"
        if core == "array": core = typestr(s.get("items")) + "[]"
        return core + opt
    if t == "array": return typestr(s.get("items")) + "[]"
    if t == "object" and "properties" in s:
        return "{" + ", ".join(f'{k}:{typestr(v)}' for k, v in s["properties"].items()) + "}"
    if "enum" in s: return f'enum({"|".join(map(str, s["enum"]))})'
    if s.get("format"): return f'{t}<{s["format"]}>'
    return t or "object"

def paramstr(op, item):
    ps = []
    for p in (item.get("parameters", []) + op.get("parameters", [])):
        if "$ref" in p: p = P[refname(p["$ref"])]
        req = "*" if p.get("required") else ""
        ps.append(f'`{p["name"]}`{req} {p["in"]}:{typestr(p.get("schema"))}')
    return "<br>".join(ps) or "—"

def bodystr(op):
    rb = op.get("requestBody")
    if not rb: return "—"
    sc = rb["content"]["application/json"]["schema"]
    return f'`{refname(sc["$ref"])}`' if "$ref" in sc else "`" + typestr(sc) + "`"

def respstr(op):
    for code, r in op.get("responses", {}).items():
        if code.startswith("2"):
            c = (r or {}).get("content", {}).get("application/json")
            return f'`{code}` data: `{typestr(c["schema"])}`' if c else f'`{code}` —'
    return "—"

tags = [t["name"] for t in d.get("tags", [])]
bytag = {t: [] for t in tags}
for path, item in d["paths"].items():
    for m, op in item.items():
        if m not in ("get", "post", "put", "patch", "delete"): continue
        bytag.setdefault((op.get("tags") or ["Other"])[0], []).append((m.upper(), path, op, item))

out = []; W = out.append
W("# Fleet Ride — API Reference\n")
W("> **Generated from `docs/openapi.yaml` — the single source of truth. Do not hand-edit; run `python3 docs/gen_api_md.py`.**")
W(f"> {sum(len(v) for v in bytag.values())} REST operations · {len(S)} schemas · Socket.IO live-trip channel.\n")
W("## Conventions\n\n| | |\n|---|---|")
W("| Base URL | `https://api.fleetride.qa/v1` |")
W("| Auth | `Authorization: Bearer <accessToken>` (Socket.IO handshake `auth:{token}`) |")
W("| Content type | `application/json; charset=utf-8` |")
W("| **Localization** | Send `Accept-Language: en` \\| `ar`. Every response echoes `locale` and localizes all human-facing fields (`message`, `name`, `subtitle`, `description`, `why`…). |")
W("| **Payments** | **Stripe.** Add card = SetupIntent → `pm_…`; pay/top-up = PaymentIntent (3DS/SCA). Raw PAN/CVV never reach Fleet Ride. |")
W("| Money | `number` (QAR, 2dp) — never a formatted string |")
W("| Pagination | `?limit&cursor` → `data.items[]` + `meta.nextCursor` |")
W("| Idempotency | `Idempotency-Key: <uuid>` on request_ride, top-up, payment-intent, add-card |\n")
W("## The one response envelope — used by EVERY REST endpoint\n")
W("The endpoint's payload is always under `data`. In the tables below, *Response* `data: X` means the body is this envelope with `data` of type `X`.\n")
W("```jsonc\n{")
for k, v in S["ApiResponse"]["properties"].items():
    W(f'  "{k}": {typestr(v)},')
W("}\n```")
W("_Error uses the same envelope: `status:false`, `statusCode>=400`, `message` localized, `data:null`, `error:{code,field}`._\n")
for tag in tags:
    rows = bytag.get(tag) or []
    if not rows: continue
    W(f"## {tag}\n")
    W("| Endpoint | Parameters | Request body | Response |\n|---|---|---|---|")
    for meth, path, op, item in rows:
        W(f'| `{meth} {path}`<br>{op.get("summary","")} | {paramstr(op,item)} | {bodystr(op)} | {respstr(op)} |')
    W("")
sio = d.get("x-socketio", {})
W("## Realtime — Socket.IO (`/rt`)\n")
W("_Raw event frames — **not** wrapped in the ApiResponse envelope._\n")
W("| Direction | Event | Payload |\n|---|---|---|")
for e, s in sio.get("clientToServer", {}).items():
    W(f'| client→server | `{e}` | `{refname(s["$ref"])}` |')
for e, s in sio.get("serverToClient", {}).items():
    W(f'| server→client | `{e}` | `{refname(s["$ref"])}` |')
W("")
W("## Appendix — schemas (attribute : datatype)\n")
W("_`*` = required · `?` = nullable · `X[]` = array of X._\n")
for name in sorted(S):
    s = S[name]
    W(f"### {name}")
    if "allOf" in s or "anyOf" in s:
        W("`" + typestr(s) + "`\n"); continue
    props = s.get("properties")
    if not props:
        W(f"`{typestr(s)}`" + (f" — {s['description']}" if s.get('description') else "") + "\n"); continue
    req = set(s.get("required", []))
    W("| attribute | type |\n|---|---|")
    for k, v in props.items():
        W(f'| `{k}`{"*" if k in req else ""} | `{typestr(v)}` |')
    W("")
open(os.path.join(ROOT, "docs/API.md"), "w").write("\n".join(out))
print("API.md regenerated:", len(out), "lines")
