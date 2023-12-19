FROM cfg_ubutnu

WORKDIR /app

COPY . /app

RUN chmod +x /app/scripts/start.sh

CMD ["./scripts/start.sh"]
