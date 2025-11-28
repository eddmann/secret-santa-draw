# 🎅 Secret Santa Draw

<a href="https://apps.apple.com/gb/app/secret-santa-draw/id6738795018"><img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" /></a>

[API](./api/) | [Client](./client/) | [iOS](./ios/)

This year's offering expands upon last year's [PWA](https://github.com/eddmann/secret-santa-pwa), transforming it into a full-fledged platform complete with an accompanying iOS application.
It brings holiday cheer by making Secret Santa draws a breeze to organise, whether gathered around the tree or connected online! 🎄

The service was built in a RAD manner, with a self-imposed time constraint of just a couple of evenings for development - driven by the goal of getting something shipped! 🚀

## Features

- **Local In-Person Draws**: Conduct offline Secret Santa draws, retaining the functionality of the [original PWA](https://github.com/eddmann/secret-santa-pwa).
- **Online Remote Draws:**
  - Register and create groups to manage annual Secret Santa draws.
  - Add participants with names and email addresses.
  - Apply allocation exclusions (e.g., couples, previous pairings).
  - Automatically populate new draws with participants and exclusions from the previous year's draw.
- **Automated Email Notifications**: Participants receive an email with their Secret Santa allocation.
- **Gift Ideas**: Participants can provide multiple gift ideas and links (up to 5), ordered by preference.
- **Anonymous Messaging**: Communicate anonymously with your Secret Santa match through built-in messaging.
- **Account Registration:**
  - Participants can optionally register with the service.
  - Allocations are linked to accounts based on email address, consolidating access to all associated draws.

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

### iOS Application

- Thin application which wraps PWA
- Swift
- Universal Link support
- Based on the great [ios-pwa-wrap](https://github.com/khmyznikov/ios-pwa-wrap) project!

### Infrastructure

- AWS CDK for IaC concerns
- Lambda Bref for Serverless API deployment
- SQS for background/asynchronous Laravel jobs
- S3 bucket for static Client deployment
- Fronted by a CloudFront distribution that manages caching concerns

## Other Years

Interested in seeing how I over-engineered the problem of allocating Secret Santa's in other years?

- [2020 - Clojure Secret Santa](https://github.com/eddmann/clojure-secret-santa)
- [2021 - Pico Secret Santa](https://github.com/eddmann/pico-secret-santa)
- [2022 - Step Function Secret Santa](https://github.com/eddmann/step-function-secret-santa)
- [2023 - Secret Santa PWA](https://github.com/eddmann/secret-santa-pwa)
- **2024 - Secret Santa Draw** ⭐
- [2025 - Secret Santa Draw Arcade](https://github.com/eddmann/secret-santa-draw-arcade)
