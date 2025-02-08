# Crunch Money

This is a (too) simple personal budgeting app that (I've been told) is similar to EveryDollar.

## Deployment

Here's a docker-compose.yml to get you started:

```yml
services:
  app:
    image: jkoop/crunch-money
    # environment:
    #   - APP_KEY=the container's log will tell you what to put here
    volumes:
      - ./storage:/storage
    ports:
      - 8080:8080/tcp
```

1. Start the container in the foreground (`docker compose up`)
2. You will be told what to set APP_KEY and the container will exit
3. Set the APP_KEY in your docker-compose.yml
4. Start the container in the background (`docker compose up -d`)
5. After few seconds, print the logs (`docker compose logs`)
6. The log will contain a login token for the first user of the system
