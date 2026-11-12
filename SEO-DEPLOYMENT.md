# Deploying the SEO repairs

Upload the changed application files and both `.htaccess` files. Use `public/` as
the document root where the hosting configuration permits it.

Set these values in the **production** `.env` (preserve the existing key and database credentials):

```dotenv
APP_NAME="Ubiquiti UniFi Kenya"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ubiquityunifikenya.co.ke
CANONICAL_URL=https://ubiquityunifikenya.co.ke
BUSINESS_NAME="Ubiquiti UniFi Kenya"
BUSINESS_LEGAL_NAME="Ubiquiti Unifi Kenya"
BUSINESS_PHONE=+254729299439
BUSINESS_EMAIL=info@ubiquityunifikenya.co.ke
BUSINESS_ADDRESS="Kimathi Street, Lyrics House"
BUSINESS_HOURS="8 AM - 5 PM"
```

These business details were supplied by the owner. Configured business phone and
email take precedence over older homepage contact settings, including in the
header and structured data. Opening hours are 8 AM to 5 PM; operating days have
not been specified. The returns page states the confirmed seven-day window.
Delivery charges, timelines, detailed return conditions and warranty terms still
need confirmation. Replace policy summaries with the actual terms in the page
editor if required; saved pages take precedence over the fallback policy copy.

Then run:

```sh
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
```

No catalogue reseed or database reset is required. Existing demo testimonials are
excluded from the storefront without deleting records. Publish only genuine
customer feedback through the testimonials editor.

Verify `/sitemap.xml` returns 200 and valid XML, `robots.txt` names this domain,
and canonical, Open Graph, breadcrumb, product offer and image URLs use the public
HTTPS domain. HTTP and www variants should redirect once with paths and queries
preserved. The Apache rules cover static files too; the middleware covers Laravel
routes. If TLS terminates at a reverse proxy, configure trusted proxy handling at
the host before enabling redirects to prevent HTTPS redirect loops.

Resubmit the sitemap in Google Search Console and inspect a homepage, category,
product and blog URL. Validate product markup in Google's Rich Results Test.
