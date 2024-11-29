# 🎅 Secret Santa Draw

[API](./api/) | [Client](./client/)

This year's offering expands upon last year's [PWA](https://github.com/eddmann/secret-santa-pwa), transforming it into a full-fledged platform complete with an accompanying iOS/Android application (coming soon).
It brings holiday cheer by making Secret Santa draws a breeze to organize and enjoy, whether gathered around the tree or connected online! 🎄

The service was built in a RAD manner, with a self-imposed time constraint of just a couple of evenings for development - driven by the goal of getting something shipped! 🚀

## Features

- **Local In-Person Draws**: Conduct offline Secret Santa draws, retaining the functionality of the [original PWA](https://github.com/eddmann/secret-santa-pwa).
- **Online Remote Draws:**
  - Register and create groups to manage annual Secret Santa draws.
  - Add participants with names and email addresses.
  - Apply allocation exclusions (e.g., couples, previous pairings).
- **Automated Email Notifications**: Participants receive an email with their Secret Santa allocation.
- **Gift Ideas Management**: Participants can provide and view gift ideas for their assigned allocation.
- **Account Registration:**
  - Participants can optionally register with the service.
  - Allocations are linked to accounts based on email address, consolidating access to all associated draws.

## Future Features:

- **Prefill Draw Entries**: Automatically populate draw participants from the previous year's draw.
- **Enhanced Gift Ideas**: Support for various idea types (URLs, images, etc.).

## 🛠️ Technical Overview

This project builds on last year's PWA by incorporating a backend API, enabling remote draw capabilities and account-based access.

### Backend API

- RESTful HAL-based API, embracing HATEOAS principles.
- Laravel 11, with Sanctum for cookie-based sessions
- PostgreSQL
- PHP 8.4
- Lambda Bref for Serverless deployment
- Email notifications sent using the Laravel Jobs queue

### Client Application

- React, with React Router
- TypeScript
- Vite
- PWA features, such as offline support
- Uses the [Ketting](https://github.com/badgateway/ketting) HTTP library to manage access to the HAL-based API

### Infrastructure

- AWS CDK for IaC concerns
- Lambda Bref for Serverless API deployment
- SQS for background/asynchronous Laravel jobs
- S3 bucket for static Client deployment
- Fronted by a CloudFront distribution that manages caching concerns
