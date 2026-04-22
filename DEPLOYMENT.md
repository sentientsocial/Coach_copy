# Deploying AI Coach PHP to Cloudways

This guide will help you deploy your AI Coach meditation app to Cloudways hosting.

## Prerequisites

- Cloudways account
- OpenAI API key
- Git repository (optional but recommended)

## Step 1: Create Cloudways Application

1. **Login to Cloudways Dashboard**
   - Go to [cloudways.com](https://cloudways.com) and login

2. **Launch New Server**
   - Click "Launch Now" or "Add Server"
   - Choose your cloud provider (DigitalOcean, AWS, etc.)
   - Select server size (1GB RAM minimum recommended)
   - Choose PHP 8.2 or 8.3
   - Select your preferred location
   - Add your application name: "AI Coach"

3. **Server Configuration**
   - Wait for server provisioning (5-10 minutes)
   - Note your server IP and application URL

## Step 2: Upload Files

### Option A: Using Cloudways File Manager

1. **Access File Manager**
   - Go to your application in Cloudways dashboard
   - Click "Access Details" → "Application URL"
   - Use the file manager in Cloudways dashboard

2. **Upload Files**
   - Delete contents of `public_html` folder
   - Upload all files from your project root to `public_html`
   - Ensure the file structure looks like this:
     ```
     public_html/
     ├── public/
     │   ├── index.php
     │   ├── .htaccess
     │   └── assets/
     ├── src/
     ├── templates/
     ├── vendor/
     ├── config/
     ├── composer.json
     └── .env.example
     ```

### Option B: Using Git (Recommended)

1. **Setup Git Repository**
   ```bash
   # In your local project directory
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin YOUR_REPOSITORY_URL
   git push -u origin main
   ```

2. **Clone on Cloudways**
   - SSH into your server (SSH details in Cloudways dashboard)
   - Navigate to application folder
   - Clone your repository

## Step 3: Configure Environment

1. **Create .env File**
   - Copy `.env.example` to `.env`
   - Update with your settings:
   ```
   APP_ENV=production
   APP_DEBUG=false
   OPENAI_API_KEY=your_actual_openai_api_key_here
   SESSION_NAME=sentient_coach_session
   SESSION_LIFETIME=7200
   ```

2. **Set File Permissions**
   - Ensure `public` folder is accessible
   - Set proper permissions for cache/session directories

## Step 4: Install Dependencies

1. **Access SSH Terminal**
   - Use Cloudways SSH terminal or connect via SSH client
   - Navigate to your application folder

2. **Install Composer Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

## Step 5: Configure Web Server

1. **Update Document Root**
   - In Cloudways dashboard, go to "Application Settings"
   - Change document root to `public` (not `public_html`)
   - Or create a symlink: `ln -s public_html/public/* public_html/`

2. **Configure Apache/Nginx**
   - Ensure URL rewriting is enabled
   - The `.htaccess` file should handle this automatically for Apache

## Step 6: Database Setup (If Needed)

Currently, the app uses sessions only. If you plan to add database functionality:

1. **Create Database**
   - Use Cloudways database manager
   - Note connection details

2. **Update Configuration**
   - Add database credentials to `.env`
   - Update config files as needed

## Step 7: SSL Certificate

1. **Enable SSL**
   - In Cloudways dashboard, go to "SSL Certificate"
   - Enable "Let's Encrypt SSL"
   - Force HTTPS redirect

## Step 8: Final Configuration

1. **Test the Application**
   - Visit your application URL
   - Test the complete question flow
   - Verify plan generation works

2. **Optimize Performance**
   - Enable caching in Cloudways
   - Configure CloudwaysBot for monitoring

## Security Considerations

1. **Environment Variables**
   - Never commit `.env` files to version control
   - Keep API keys secure
   - Use strong session configurations

2. **File Permissions**
   - Restrict access to sensitive files
   - The `.htaccess` file includes security headers

3. **Regular Updates**
   - Keep PHP and dependencies updated
   - Monitor for security patches

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check error logs in Cloudways dashboard
   - Verify file permissions
   - Ensure all dependencies are installed

2. **API Key Issues**
   - Verify OpenAI API key is correct
   - Check API quota and billing

3. **Session Problems**
   - Ensure session directory is writable
   - Check session configuration

4. **URL Rewriting Issues**
   - Verify `.htaccess` is in the correct location
   - Check if mod_rewrite is enabled

### Log Files

- **Application Logs**: Available in Cloudways dashboard
- **Error Logs**: Check PHP error logs
- **Access Logs**: Monitor traffic patterns

## Environment Variables Reference

```bash
# Application
APP_ENV=production
APP_DEBUG=false

# OpenAI
OPENAI_API_KEY=sk-your-key-here

# Session
SESSION_NAME=sentient_coach_session
SESSION_LIFETIME=7200
```

## Support

For additional support:
- Cloudways documentation
- PHP/Composer documentation
- OpenAI API documentation

## Post-Deployment Checklist

- [ ] Application loads without errors
- [ ] Questions flow works correctly
- [ ] Plan generation functions
- [ ] SSL certificate is active
- [ ] Performance is acceptable
- [ ] Error monitoring is set up
- [ ] Backups are configured
- [ ] Domain name is configured (if applicable) 