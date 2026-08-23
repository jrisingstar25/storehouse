# Vendored front-end libraries

Third-party libraries are served from here rather than a CDN, so the app has
no runtime dependency on an external host and works on a machine with no
internet access.

| Library     | Version | Files                                            | Source |
| ----------- | ------- | ------------------------------------------------ | ------ |
| Bootstrap   | 5.3.3   | `bootstrap/css/bootstrap.min.css` (+ `.map`), `bootstrap/js/bootstrap.bundle.min.js` (+ `.map`) | https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/ |
| jQuery      | 3.7.1   | `jquery/jquery.min.js`                            | https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/ |
| Fancybox    | 5.0.36  | `fancybox/fancybox.css`, `fancybox/fancybox.umd.js` | https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.36/dist/fancybox/ |

Notes:

- The files are unmodified. The `.map` files are included because the
  minified Bootstrap files reference them; jQuery 3.7.1's minified build
  carries no `sourceMappingURL`, so no map is shipped for it.
- Nothing here fetches anything at runtime. The only external URLs inside
  these files are licence banners in comments and the SVG XML namespace
  (`http://www.w3.org/2000/svg`) inside inline data URIs, neither of which is
  a network request.
- To upgrade, download the same file names from the URLs above and bump the
  versions in this table. Bootstrap and jQuery are referenced from
  `application/views/layouts/`, Fancybox from `admin/Doctors.php`, the only
  screen that uses it.

Fancybox is GPLv3 for open-source use and needs a commercial licence
otherwise - see the note in the main README.
