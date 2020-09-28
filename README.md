# gosearch-searchengine-api + Implementasi query boolean pada sistem search

LINK DEMO: https://subtle-torus-235508.web.app/

Contoh Query: https://subtle-torus-235508.web.app/search/unisbank/1

https://subtle-torus-235508.web.app/search/implementasi%20jaringan%20komputer/1

LINK SOURCE CODE FRONTEND: https://github.com/nimegami01/sistem-temu-kembali-gosearch

By:
1. Wira Dwi Susanto (NIM: 17.01.53.0053)
2. Sativa Wahyu Priyanto (NIM: 17.01.53.0052)
3. Berliana Siwi Humandari (NIM: 17.01.53.0103)

Endpoint API:
1. File Upload: https://www.kmsp-store.com/gosearch/api/v1/file_upload (POST), Format -> multipart/form-data
Request Body:

document

search_title

user

Request Headers:

GOSEARCH-API-KEY: ab01c58f-606d-4739-855c-c86f1107a536

Authorization: Basic N2Q5NmY2OTc5MDMxOWNmNmM1ZmViMjU4NDllYjQ0ODU6MGFhYmZkYjEwN2EwYTBjYmI0YTVlYTk3MjQyOTZjZGM=

2. Search Query: https://www.kmsp-store.com/gosearch/api/v1/search_query (POST)

Request Body:

search_query

Request Headers:

GOSEARCH-API-KEY: ab01c58f-606d-4739-855c-c86f1107a536

Authorization: Basic N2Q5NmY2OTc5MDMxOWNmNmM1ZmViMjU4NDllYjQ0ODU6MGFhYmZkYjEwN2EwYTBjYmI0YTVlYTk3MjQyOTZjZGM=

Content-Type: application/x-www-form-urlencoded

3. Latest Upload: https://www.kmsp-store.com/gosearch/api/v1/latest_upload (GET)

Request Headers:

GOSEARCH-API-KEY: ab01c58f-606d-4739-855c-c86f1107a536

Authorization: Basic N2Q5NmY2OTc5MDMxOWNmNmM1ZmViMjU4NDllYjQ0ODU6MGFhYmZkYjEwN2EwYTBjYmI0YTVlYTk3MjQyOTZjZGM=

Content-Type: application/x-www-form-urlencoded

Wajib instal dan update composer dengan command:
composer update -vv
