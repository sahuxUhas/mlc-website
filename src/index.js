export default {
  async fetch(request, env) {
    // Serve static assets from legacy-demo (or public) - Cloudflare will handle via ASSETS binding
    // Fallback to assets
    return env.ASSETS ? env.ASSETS.fetch(request) : new Response("Mahalchari News - Static deployment active. For Laravel full version, use cPanel PHP hosting.", { status: 200, headers: { "Content-Type": "text/html; charset=utf-8" } });
  }
}
