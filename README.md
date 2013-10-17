## Topline Yola WHMCS Module by Hawk Host

The Topline Yola WHMCS module allows you to create site builder accounts from WHMCS and offer them to your users.

## Setup

Upload the files straight to your WHMCS installation.  By default the templates for the module are placed in the portal folder.  If you have an alternative
template please upload them there.  Once you've uploaded your files login to your admin area of WHMCS and go to `Setup -> Addon Modules`
and activate the `Topline - Yola addon module`.  Once this has been completed you now need to add a server in WHMCS.  Go to `Setup -> Products/Services -> Servers` and click `Add New Server`.  On this page go down to Type and select `Toplineyola` and put in your partner username and for the access hash put your partner GUID

### Creating a Product

Go to `Products/Services -> Products/Services` and use the create a new product option. On this page set your product type to other and Uncheck require domain option as it is not needed.

Then go to `Module Settings` and for Module Name pick `Toplineyola`.  Once the page refreshes set your bundle name (these are from the "Yola Bundles" or if you have a custom one from them use that):

* yola_3pg
* yola_unlim
* yola_prem
* yola_prem_estore
* ~~yola_seo~~ (Coming soon)
* ~~yola_seo_ecom~~ (Coming soon)

Configure the rest as you would any other product you would in WHMCS.

### Creating a Bundle

If you wish to bundle the service with a hosting account you will go to `Products/Services -> Product Bundles`.  On this page you will use the create a new bundle option and add your hosting account as well as the site builder service.  You will use the order link provided on this page as the link given to users.

### Creating a Trial Service

You will follow the same instructions as you were when you created a product.  The difference will be with the Trial is you will check off that it will be a trial account and set your durection of the trial.  You will then need a domain or subdomain and hosting account on a server to upload your contents.  The rest of the page should walk you through the trial setup process.