# Universal Agent Guidelines

## Scope
- These instructions apply across repositories unless a repo-local file explicitly overrides them.
- Prefer clarity and pragmatism over cleverness.

## HTML
- Use semantic, minimal markup.
- Keep structure document-first and avoid presentational HTML.
- Ensure accessibility basics are covered (labels, alt text, heading order).

## CSS
- Prefer a single global stylesheet unless a repo explicitly defines another approach.
- Avoid inline CSS.
- Keep class names descriptive and stable.
- Favor layouts that read well as documents; responsiveness is important but secondary to readability.

## JavaScript
- JavaScript is optional; the product must remain usable without it.
- Use JS to enhance usability or automate boilerplate, not to duplicate server state.
- Avoid heavy client-side state or frameworks unless the repo explicitly adopts them.

## PHP
- Keep routing, controllers, and rendering separated.
- Keep business logic out of templates.
- Keep view logic focused on presentation only.

## Git
- Use short, imperative, sentence-case commit messages.
- Keep commits focused on a single intent and user-visible outcome.
- Avoid mixing formatting-only edits with functional changes when possible.
- Do not include unrelated files in a commit.

## Comments
- Use comments to explain intent, constraints, or non-obvious choices.
- Avoid redundant comments that restate code.
- Track TODOs centrally when possible; avoid TODO sprawl.

## File Headers
- Add a clear, informative header when the file is non-trivial and the format allows comments.
- Do not add comment headers to formats where comments are inappropriate (for example, `.json`).
- Headers should explain purpose, scope, and ownership of intent.
- Do not include edit history, authorship, dates, or per-file version numbers.
- Keep headers short, stable, and aligned with the file’s actual role.
- Use format-appropriate conventions when tooling exists.
