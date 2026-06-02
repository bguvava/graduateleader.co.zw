# Contact Form Email Debugging Guide

## Issue
Contact form submissions are not being received in the info@graduateleader.co.zw mailbox.

## Diagnostic Steps

### Step 1: Test Server Mail Function

1. Upload `test-email.php` to your server (in the same `/php/` folder)
2. Access it in your browser: `https://graduateleader.co.zw/php/test-email.php`
3. Check the results:
   - **Test 1** should show "SUCCESS" if mail() works
   - Check the PHP mail configuration displayed
   - Note the server information

**What the results mean:**

- ✅ **Test 1 = SUCCESS + Email arrives** → Server mail works, form handler needs fixing
- ⚠️ **Test 1 = SUCCESS + No email** → Server mail configured incorrectly (emails sent but not delivered)
- ❌ **Test 1 = FAILED** → mail() function is disabled on your server

### Step 2: Check Form Handler Logs

After testing the contact form, two log files will be created in the `/php/` folder:

1. **`contact-form-debug.log`** - Detailed submission logs
2. **`contact-form-errors.log`** - PHP errors (if any)

**Download these files** and check what they show:

```
=== Form submission started ===
Form type detected: general
POST data: {...}
Preparing to send email:
  To: info@graduateleader.co.zw
  Subject: ...
  ...
mail() function returned: TRUE (success)
SUCCESS: Email sent successfully
=== Form submission ended ===
```

**What to look for:**

- Does it show "mail() function returned: TRUE"?
- Are there any ERROR messages?
- Does the POST data look correct?

### Step 3: Common Server Issues

#### Issue A: mail() Function Disabled
**Symptoms:** Test 1 shows FAILED

**Solution:** Your hosting provider has disabled the mail() function. You need to use SMTP instead.

**Fix:** I can help you implement PHPMailer with SMTP authentication.

#### Issue B: Server Not Configured to Send Mail
**Symptoms:** Test 1 shows SUCCESS but no email arrives

**Possible causes:**
- Server has no sendmail/postfix configured
- Outbound mail ports (25, 587, 465) are blocked
- Domain email authentication missing (SPF, DKIM)
- Server IP blacklisted

**Fix Options:**
1. Contact your hosting provider to enable outbound email
2. Use SMTP with authenticated email account
3. Use a third-party email service (SendGrid, Mailgun, etc.)

#### Issue C: Emails Going to Spam
**Symptoms:** Emails are sent but not in inbox

**Check:**
- Spam/Junk folder at info@graduateleader.co.zw
- Check email headers for spam score

**Fix:**
- Add SPF record for your domain
- Add DKIM signature
- Use authenticated SMTP

#### Issue D: Wrong From Email Address
**Symptoms:** Server rejects noreply@graduateleader.co.zw

**Solution:** Change the FROM_EMAIL in contact-form-handler.php to a real email address that exists on your server (e.g., info@graduateleader.co.zw or admin@graduateleader.co.zw)

### Step 4: Check cPanel Email Settings

If you're using cPanel:

1. Go to **cPanel → Email Deliverability**
2. Check if your domain shows a green checkmark
3. Look for any warnings or errors
4. Fix any SPF or DKIM issues

### Step 5: Test with SMTP (Recommended Solution)

If mail() doesn't work reliably, SMTP is the professional solution used by most production sites.

**Advantages:**
- ✅ More reliable
- ✅ Better deliverability
- ✅ Authentication prevents spoofing
- ✅ Detailed error messages
- ✅ Works on any server

I can help you implement PHPMailer with SMTP if needed.

## Quick Actions

### Action 1: Upload and Run Test
```bash
# Upload these files to your server:
- php/test-email.php
- php/contact-form-handler.php (updated with logging)

# Then visit in browser:
https://graduateleader.co.zw/php/test-email.php
```

### Action 2: Submit Test Form
1. Go to https://graduateleader.co.zw/contact.html
2. Fill out the general enquiry form
3. Submit it
4. Download and check `php/contact-form-debug.log`

### Action 3: Share Results
Send me:
- Screenshot of test-email.php results
- Contents of contact-form-debug.log
- Any relevant error messages from server

## Next Steps

Based on the test results, I'll help you:

1. **If mail() works** → Fix any configuration issues in the form handler
2. **If mail() doesn't work** → Implement SMTP solution with PHPMailer
3. **If emails go to spam** → Fix domain authentication (SPF/DKIM)

---

**Need immediate help?** Share the test results and I'll provide a targeted solution.
