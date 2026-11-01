# Notes for Claude

Static Spanish site for Factor H Comunicaciones (factorh.co), published to Hostinger by .github/workflows/deploy.yml on every push to main. The site speaks in tú.

- Texts and photos: `content/<page>.yml`, as `group: key: "texto"`; photos are plain paths under `img/` (files in `static/img/`). `content/site.yml` holds the menu, footer and the contact email (`datos.correo`, also the contact form's destination).
- Markup in texts: `**bold**`, `_em_`, `[[x]]` (the highlighted span the template defines), newline = `<br>`.
- Layout: `src/templates/<page>.html` with `{{t:group.key}}`, `{{t:group.key|<span …>}}`, `{{a:group.key}}` (attributes), `{{img:group.key}}`; `site.` prefix reads content/site.yml. Key prefix = element kind (h1, p, a, span, foto, alt…), which the editor uses as label.
- Everything in `static/` is copied as is (CSS/JS in `static/assets/`, .htaccess, robots, sitemap, favicons, og.png). `src/templates/enviar.php` is the contact form handler.
- After adding or removing a slot in a template, add the key to the content file and run `python3 src/cms_config.py` so the editor (Pages CMS, `.pages.yml`) shows it.
- Build check: `python3 src/build.py` (needs pyyaml). `site/` is generated and gitignored.
- Ethics (Ley 23 de 1981, Ley 1581 de 2012): no promises of results, no prices as hooks, no patient testimonials without written consent.
- For a small text or photo change, edit only the relevant content file.
