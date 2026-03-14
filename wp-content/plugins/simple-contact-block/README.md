# simple-contact-block

A Gutenberg block plugin that renders a configurable contact form and sends validated submissions to a configured recipient email. Optional database storage can be enabled in settings.

## Features
- Contact form fields: name, email, optional subject, message
- Admin email delivery with Reply-To set to the visitor
- Anti-spam: nonce, honeypot, minimum submit time
- Optional database storage with export/delete
- Settings page for recipient, messages, and confirmation email

## Admin Settings
- Recipient email
- Sender name
- Success and failure messages
- Store submissions (toggle)
- Send confirmation email (toggle)
- Confirmation subject and body

## Notes
- Storage is disabled by default.
- When storage is enabled, submissions are stored in a custom database table created on demand.
- Validation errors are returned to the form via a redirect status and displayed inline.
