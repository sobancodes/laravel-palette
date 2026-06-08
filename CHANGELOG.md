# Changelog

All notable changes to this package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2024-XX-XX

### Added

- `PaletteManager` service with `extract`, `mostUsed`, `palette`, and `colors` methods
- `Palette` facade
- Storage disk integration via `fromDisk`
- Transparent image handling via `withBackground`
- Optional result caching with configurable TTL and prefix
- `ExtractColors` queueable job with automatic model updates
- `ColorsExtracted` event dispatched after extraction
- `ColorPalette` Eloquent cast for JSON column storage
- `ExtractsPalette` trait for convenient model integration
- `HasMinimumColors` validation rule
- Publishable config file
