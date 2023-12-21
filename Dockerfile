FROM cfg_ubuntu

WORKDIR /app

ARG AUTH_TOKEN

COPY . /app

RUN composer install

RUN /ngrok config add-authtoken $AUTH_TOKEN

RUN chmod +x /app/scripts/start.sh

# To build this image, run:
# where AUTH_TOKEN is your ngrok auth token
# sudo docker build --build-arg AUTH_TOKEN= -t your_image .
