const nodemailer = require('nodemailer');

// Netlify form submission event handler
exports.handler = async function(event) {
  const payload = JSON.parse(event.body || '{}');

  // Only process form submissions
  if (payload?.event !== 'submission-created') {
    return { statusCode: 200, body: 'Ignored non-submission event' };
  }

  const data = payload?.payload?.data || {};

  const name = data.name || 'Unknown';
  const email = data.email || 'no-email-provided';
  const subject = data.subject || 'Website contact';
  const message = data.message || '';
  const formName = data['form-name'] || payload?.payload?.form_name || 'contact';

  // SMTP credentials (set these in Netlify environment variables)
  const user = process.env.GMAIL_USER;
  const pass = process.env.GMAIL_PASS;
  const to = process.env.GMAIL_TO || 'efren.cavazos@gmail.com';

  if (!user || !pass) {
    console.error('Missing GMAIL_USER or GMAIL_PASS env vars');
    return { statusCode: 500, body: 'Email not configured' };
  }

  // Gmail SMTP transporter (requires App Password when 2FA is enabled)
  const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
      user,
      pass
    }
  });

  const textBody = [
    'New form submission',
    `Form: ${formName}`,
    `Name: ${name}`,
    `Email: ${email}`,
    `Subject: ${subject}`,
    '',
    'Message:',
    message
  ].join('\n');

  try {
    await transporter.sendMail({
      from: `"Efren Cavazos Contact" <${user}>`,
      to,
      replyTo: email,
      subject: subject || 'Website contact',
      text: textBody
    });
    return { statusCode: 200, body: 'Email sent' };
  } catch (err) {
    console.error('Failed to send email', err);
    return { statusCode: 500, body: 'Failed to send email' };
  }
};
