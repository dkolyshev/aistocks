# How to deploy the app on the DigitalOcean droplet

Create a DO droplet and navigate to the droplet's dashboard. Open an in-built console with root access to the droplet terminal.

### Install Docker using the official guide

[Click here - how to install Docker](https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository)

You can just do these two steps:

1. Set up Docker's apt repository.
2. Install the Docker packages.

### Create apps directory

```bash
cd ~ && mkdir ~/apps && cd ~/apps
```

### Clone the repository

```bash
git clone https://github.com/dkolyshev/aistocks.git
```

### Fix permissions

```bash
chown -R 33:33 aistocks
```

```bash
chmod -R 775 aistocks
```

### Build & run a Docker container with app

```bash
cd ~/apps/aistocks/.docker/digitalocean
```

```bash
docker compose up -d
```

Wait till the container is running.

### Check the app

Make a note that the app is running on port 8080.
Now you can open the droplet's dashboard, find the IP address of your droplet, and use it as the entry point of the app:

http://YOUR-DROPLET-ADDRESS:8080

For example, this is how my droplet address looks:

http://178.128.226.190:8080/
