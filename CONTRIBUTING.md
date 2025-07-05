# Contributing to Lara Dashboard

First off, thank you for considering contributing to Lara Dashboard! It's people like you that make Lara Dashboard such a great tool. This document provides guidelines and steps for contributing.

## Code of Conduct

By participating in this project, you are expected to uphold our Code of Conduct. Please report unacceptable behavior to [manirujjamanakash@gmail.com](mailto:manirujjamanakash@gmail.com).

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find that you don't need to create one. When you are creating a bug report, please include as many details as possible.

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. Create an issue and provide the necessary information regarding the enhancement.

### Pull Requests

Pull requests are the best way to propose changes to the codebase. We actively welcome your PRs.

## Development Guidelines

When contributing code to Lara Dashboard, please follow these guidelines:

### Architectural Principles

1. **Separation of Concerns**
   - View/controller logic should be separated in most cases
   - Business logic should reside in Service classes
   - Controllers should be thin and primarily handle HTTP requests and responses

2. **Validation**
   - All request validation should be in dedicated classes in `Http/Requests/` directory
   - Never validate directly in controllers

3. **Internationalization (i18n)**
   - All user-facing strings should use Laravel's translation functions (`__()` or `@lang()`)
   - New strings should be added to the appropriate translation files

4. **Responsive Design**
   - All UI components must be responsive and work on mobile devices
   - Test your changes on multiple screen sizes before submitting

5. **Theme Support**
   - Ensure all UI changes work in both light and dark modes
   - Use Tailwind's dark mode variants where appropriate

6. **Authorization**
   - All actions should be properly secured with appropriate permissions
   - Use our permission system consistently throughout the code

7. **Extensibility**
   - If implementing functionality that might be useful for extension, create action/filter hooks
   - Use the `ld_do_action()` and `ld_apply_filters()` functions for extension points

### Coding Standards

1. **PHP Code Style**
   - Follow PSR-12 coding standards
   - Use type hints for parameters and return types
   - Use strict typing where possible
   - Document classes and methods with PHPDoc comments

2. **JavaScript and CSS**
   - Follow the existing code style
   - Use ES6 features when appropriate
   - Prefer Tailwind classes over custom CSS

3. **Naming Conventions**
   - Controllers: Plural, PascalCase (e.g., `UsersController`)
   - Models: Singular, PascalCase (e.g., `User`)
   - Services: Singular with Service suffix, PascalCase (e.g., `UserService`)
   - Database: Use snake_case for tables and columns

4. **File Organization**
   - Keep files in their appropriate directories
   - Follow Laravel's directory structure conventions
   - Module-specific code should go in the appropriate module directory

### Testing

1. **Unit Tests**
   - Write tests for new features and bug fixes
   - Ensure all tests pass before submitting your PR
   - Aim for a high test coverage

2. **Manual Testing**
   - Test your changes in both light and dark modes
   - Test on different screen sizes
   - Verify authorization works correctly

## Pull Request Process

1. Fork the repository and create your branch from `main`
2. If you've added code that should be tested, add tests
3. Ensure the test suite passes
4. Make sure your code follows the coding standards
5. Update the documentation if necessary
6. Issue the pull request

### PR Checklist

Before submitting your PR, please ensure:

- [ ] Your code follows the style guidelines of this project
- [ ] You have separated view/controller logic appropriately
- [ ] Request validation is in dedicated Request classes
- [ ] Business logic is in Service classes
- [ ] All strings are translatable
- [ ] Changes work in both light and dark modes
- [ ] UI is responsive on all screen sizes
- [ ] Authorization and permissions are properly implemented
- [ ] You've added hooks for extension points if applicable
- [ ] You've updated documentation if necessary
- [ ] You've added tests for your changes
- [ ] All tests pass

## Development Setup

See the [README.md](README.md) for instructions on setting up the development environment.

## License

By contributing, you agree that your contributions will be licensed under the project's license.

## Questions?

Don't hesitate to reach out if you have any questions. You can contact the maintainers at [manirujjamanakash@gmail.com](mailto:manirujjamanakash@gmail.com).

Thank you for contributing to Lara Dashboard!
