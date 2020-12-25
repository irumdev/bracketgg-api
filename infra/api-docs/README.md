## API-DOCS 실행방법

```bash
docker build -f ./infra/api-docs/Dockerfile -t bracket-gg-api-docs:latest . && 
docker run -p 9968:9968 -it bracket-gg-api-docs -d
```
