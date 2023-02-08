# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

- [#134](https://github.com/os2display/display-api-service/pull/134) Fix bug where `JWT_SCREEN_REFRESH_TOKEN_TTL` value is not used when refresh token is renewed 
- [#132](https://github.com/os2display/display-api-service/pull/132) 
Added `RefreshToken` entity to fix migrations error
- [#135](https://github.com/os2display/display-api-service/pull/135) 
Updated code styles

## [1.2.1] - 2023-02-02
- Update composer packages, CVE-2022-24894, CVE-2022-24895

## [1.2.0] - 2023-01-05

- [#130](https://github.com/os2display/display-api-service/pull/130)
Added changelog.
Added github action to enforce that PRs should always include an update of the changelog.
- [#129](https://github.com/os2display/display-api-service/pull/129)
Downgraded to Api Platform 2.6, since 2.7 introduced a change in serialization. Locking to 2.6.*
- [#127](https://github.com/os2display/display-api-service/pull/127)
Updated docker setup and actions to PHP 8.1.
Updated code style.
- [#128](https://github.com/os2display/display-api-service/pull/128)
Added ttl_update: true config option for jwt refresh bundle.
Added refresh_token_expiration key to respone body.
- [#124](https://github.com/os2display/display-api-service/pull/124)
Created ThemeItemDataProvider instead of
ThemeOutputDataTransformer, to make theme accessible in the client on shared slides.
Made it possible for editors to view themes and connect them to slides: security: 'is_granted("ROLE_SCREEN") or
is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")'.
- [#126](https://github.com/os2display/display-api-service/pull/126)
Added config option for setting token TTL for screen users.
- [#123](https://github.com/os2display/display-api-service/pull/123)
Updated fixtures.
- [#125](https://github.com/os2display/display-api-service/pull/125)
Changed error handling to not always return empty array even though it is only one resource that reports error.
Added error logging.
- [#122](https://github.com/os2display/display-api-service/pull/122)
Updated docker setup to match new itkdev base setup.
- [#121](https://github.com/os2display/display-api-service/pull/121)
Changed load screen layout command to allow updating existing layouts.

## [1.1.0] - 2022-09-29

- [#120](https://github.com/os2display/display-api-service/pull/120)
Fixed path for shared Media.
- [#119](https://github.com/os2display/display-api-service/pull/119)
KOBA feed source: Changed naming in resource options. Sorted options.

## [1.0.4] - 2022-09-05

- [#117](https://github.com/os2display/display-api-service/pull/117)
Removed screen width and height. Added resolution/orientation.

## [1.0.3] - 2022-09-01

- Changed docker server setup.

## [1.0.2] - 2022-09-01

- Changed docker server setup.

## [1.0.1] - 2022-09-01

- Changed docker server setup.

## [1.0.0] - 2022-05-18

- First release.
