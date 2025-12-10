# Views Organization Chart

This module is used to draw an Organizational Chart, which is a diagram that
describes the structure of an organization and the relationships and
relative rankings of departments and positions within the organization.

## How to use:
Create views with entity type that you want to show
- It requires 2 fields (1 to display name, 1 for hierachi)
- If your view with taxonomy add parent field
- Uf your view is a user entity you must have a user reference field,
add this as a parent field
- If your view is a node entity, you must have a content reference field,
add this as a parent field another field (Title, Color, Avatar field) is
optional
