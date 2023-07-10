<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Tutoring Backend 사전과제

1. 사용 도구
- Laravel 8
- MySQL
- Composer
- Postman

2. 사용 기술
- Laravel Migrate RDB 설계
- Laravel Factory DB 생성
- Service Layer 패턴 API 작성

3. 사용 외부패키지
- Cloudniary 파일 저장
- Telegram 메세지 전송
- FCM PUSH 알림 기능 추가


## 요구사항 이외 추가 고려 설계

- 튜터링 지원 언어 확장가능성을 고려하여 DB 설계
- 관리자/튜터에게 각각 알림을 전송할 수 있는 기능 추가
- 튜터링 앱 내 회원들의 앱설정 기능을 고려하여 테이블 추가
- 튜터들이 수업내역 기반으로 정산을 받을 수 있도록 정산테이블 추가

## 기타 전달 내용

- 프로젝트 환경설정 방법

1. 프로젝트 다운로드 후 composer install 명령어를 실행하여 composer를 설치합니다.
2. .env 파일을 추가한 뒤 php artisan key:generate 명령어를 통해 APP_KEY를 생성하고 DB 환경에 맞게 값을 설정합니다.
3. php artisan migrate와 php artisan db:seed 명령어를 실행하여 데이터베이스 테이블과 더미데이터를 생성합니다.
4. php artisan serve 명령어로 로컬환경에서 서버를 가동합니다.

- 제약사항

1. 프로젝트 내 사용된 외부패키지(Cloudinary, Telegram, FCM)를 사용하기 위해서 각 KEY값이 필요합니다.


## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
