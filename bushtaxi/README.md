# Bushtaxi

## Run tests

```
cd bushtaxi
for f in $(find tests/*.yml); do docker-compose -f $f up; done
```