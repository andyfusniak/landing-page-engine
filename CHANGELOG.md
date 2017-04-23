# CHANGELOG
## 2.17.0 (23 Apr 2017)
- AJAX HTTP POST with JSON response

## 2.16.0 (14 Apr 2017)
- Optional redirect for form _next=no-redirect
- Returns a "201 Created" response for no-redirect successful requests

## 2.15.0 (21 Mar 2017)
- Make LPE compatible with PHP5.4 or above

## 2.14.0 (10 Mar 2017)
- Prevent duplicate phone numbers being captured

## 2.13.1 (13 Feb 2017)
- Fix hard coding of GA tracking code in Twig GaTrackingCode global

## 2.13.0 (10 Feb 2017)
- {{ theme_name }} Twig global

## 2.12.0 (8 Feb 2017)
- <field optional="true"> attribute added to skip validator chains for empty fields

## 2.11.0 (6 Feb 2017)
- {{ version_string }} for use with resource caching

## 2.10.3 (3 Feb 2017)
- Bugfix SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens

## 2.10.2 (2 Feb 2017)
- Bugfix for skip auto theme activation loggin

## 2.10.1 (1 Feb 2017)
- Bugfix error for missing <theme> element config.xml

## 2.10.0 (31 Jan 2017)
- UTM tracking via session and Google Analytics analytics.js JavaScript
- <ga-tracking-id> element for config.xml

## 2.9.2 (23 Jan 2017)
- Removes artificial delay and breadcrumb (var_dump)

## 2.9.1 (23 Jan 2017)
- Fix filters applied to HTTP POST params. Special case for phone number strip leading 0
- [7: Fix filters applied to HTTP POST params. Special case for phone number strip leading 0](https://bitbucket.org/sudtanadevteam/landing-page-engine/issues/7/filter-chain-not-evaluated-in-the-form)

## 2.9.0 (23 Jan 2017)
- <host> section has its own <profile>name</profile element and <databases> is repalced by <profiles> section
- Fixes bug for HTTP POST trying to update stage in DB even when no-capture is true
- Fixes bug for PDO timeout and connection based on host context
- Status page to show last inserted rows
- Klaviyo API feed and config.xml <feeds> <klaviyo> sections
- New ThaiPhone validator handling support for mobile and

## 2.8.0 (15 Jan 2017)
- Bugfix to show status-page even if the theme.xml config is broken
- Twig globals theme and theme_assets
- Fixes broken phpunit test
- Routes have names and form name uses route names
- Fix bug with HTTP POST for error display of same twig template

## 2.7.0 (28 Dec 2016)
- Application config has preset defaults (no need to developer to use a config.php file)
- Developer config/config.xml file to override default application config and set hosts and database profiles
- Extra /status-page components for runtime analysis
- More PHPUnit test

## 2.6.4 (19 Dec 2016)
- Work around temp fix for array to string conversion error when posting using checkboxes

## 2.6.3 (19 Dec 2016)
- Critial Bugfix for failed update on second HTTP POST of form data

## 2.6.2 (18 Dec 2016)
- Fix various bugs including theme.xml loadng issues when a missing section form
- Fix status-page PHP notice on Mac
- Fix HTTP form POST writing to DB (introduced in 2.6 with XML theme config)

## 2.6.1 (18 Dec 2016)
- Uses the symfony/symfony full stack instead of cherry picking symfony components

## 2.6.0 (18 Dec 2016)
- Complete overhaul of theme config loading include switch to XML

## 2.5.0 (16 Dec 2016)
- Removal of /process-post route.  In its place HTTP POSTs are sent back to the page whence they came.
- Improved logging for form fields including validators
- Fix for empty forms producing a PHP error.  Empty forms now progress without database update.
- Remove the need for _url in HTML forms

## 2.4.0 (16 Dec 2016)
- Redirects for routing

## 2.3.0 (15 Dec 2016)
- System, Landing Page Engine and Database panels for /status-page show real data
- Bugfix [1](https://bitbucket.org/sudtanadevteam/landing-page-engine/issues/1/http-post-on-fieldless-forms-causes-error) additional fix

## 2.2.0 (13 Dec 2016)
- Add UtmQueryParams {{ utm_query_params }} urlencoded googlet tracking Twig global

## 2.1.1 (12 Dec 2016)
- Fix UTF-8 database writing columns
- Fix checkbox array items to json_encoded strings in db
- Fix empty filter and validator chains in theme.json being accepted

## 2.1.0 (12 Dec 2016)
- Automatic creation of project_root/var/{twig_cache,log} directories and permissions
- Automatic activation of themes based on config.php file (symlinks from public/assets to themes)

## 2.0.1 (8 Dec 2016)
- Bootstrap based status page (not functional. visual only)

## 2.0.0 (8 Dec 2016)
- Validators and Filters including chains (with Phpunit tests)
- Change of format for theme.json file
- Multi-site support using config.php site to theme map
- Removes use of activetheme symlink in place of
  symlink from public/assets/<theme-name> to
  themes/<theme-name>/assets/<theme-name>
- Auto capture of request data stage, user_agent, referer,
  scheme, host, theme, route_config and remote_addr

## 1.1.0 (6 Dec 2016)
- Adds 'thai_date' global to all Twig templates that returns in the format of "6 ธันวาคม 2559".

## 1.0.0 (6 Dec 2016) Stable
This is the first release of the Landing Page Engine (LPE).  It contains basic features for rapid landing page construction.

Features include:
- HTTP POST form handling at fixed /process-post endpoint
- Session and cookie management
- Data capture for single and multi-page forms
- Twig templated themes
- Theming with name, version, routes and forms sections.

