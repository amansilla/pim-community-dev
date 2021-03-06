@javascript
Feature: Filter products with multiples multiselect filters
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples multiselect filters

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code    | label-en_US | type                    | useable_as_grid_filter | group |
      | color   | Color       | pim_catalog_multiselect | 1                      | other |
      | company | Company     | pim_catalog_multiselect | 1                      | other |
    And the following family:
      | code      | attributes    |
      | furniture | color,company |
      | library   | color,company |
    And the following "color" attribute options: Black and Green
    And the following "company" attribute options: RedHat, Canonical and Suze
    And the following products:
      | sku    | family    | company   | color |
      | BOOK   | library   |           |       |
      | MUG-1  | furniture | Canonical | Green |
      | MUG-2  | furniture | Suze      | Green |
      | MUG-3  | furniture | Suze      | Green |
      | MUG-4  | furniture | Suze      | Green |
      | MUG-5  | furniture |           | Green |
      | POST-1 | furniture | Suze      |       |
      | POST-2 | furniture | Suze      |       |
      | POST-3 | furniture | RedHat    |       |
    And I am logged in as "Mary"
    And I am on the products grid

  @critical
  Scenario: Successfully filter products with the same attributes
    Given I show the filter "company"
    And I filter by "company" with operator "in list" and value "Suze"
    Then I should be able to use the following filters:
      | filter | operator     | value | result                 |
      | color  | in list      | Green | MUG-2, MUG-3 and MUG-4 |
      | color  | is empty     |       | POST-1 and POST-2      |
      | color  | is not empty |       | MUG-2, MUG-3 and MUG-4 |

  Scenario: Successfully filter products without common attribute
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Green"
    Then I should be able to use the following filters:
      | filter  | operator     | value     | result                     |
      | company | in list      | Canonical | MUG-1                      |
      | company | is empty     |           | MUG-5                      |
      | company | is not empty |           | MUG-1, MUG-2, MUG-3, MUG-4 |

  Scenario: Successfully filter only one product
    Given I am on the products grid
    When I show the filter "company"
    And I filter by "company" with operator "in list" and value "Canonical"
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Green"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
