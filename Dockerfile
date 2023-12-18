FROM ubuntu

ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /app
COPY . /app

RUN apt-get update && apt-get install -y php php-mysql php-curl composer php-dom curl
RUN curl -o ngrok.zip -L https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip && unzip ngrok.zip && rm ngrok.zip

RUN composer install

EXPOSE 82
RUN ./ngrok authtoken 2ZZj21Kee9JkxCgNetS8exKsMfK_86t1rTBnc2TxWWWPqHmkC

COPY scripts/start.sh /app/start.sh

CMD ["/app/start.sh"]



