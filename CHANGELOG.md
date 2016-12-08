# CHANGELOG
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

