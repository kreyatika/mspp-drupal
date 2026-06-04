<?php

namespace Drupal\publications\Service;

use Drupal\Core\Database\Connection;

/**
 * Service for fetching publications from tbl_documentation.
 */
class PublicationsApiService {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Returns a safe ORDER BY clause from the sort key.
   */
  protected function buildOrderBy(string $sort): string {
    return match($sort) {
      'date_asc'   => 'd.Date_publication ASC',
      'title_asc'  => 'd.Titre_pub ASC',
      'title_desc' => 'd.Titre_pub DESC',
      default      => 'd.Date_publication DESC',
    };
  }

  /**
   * Builds the WHERE clause and params array from filters.
   */
  protected function buildWhere(array $filters): array {
    $where  = ['1 = 1'];
    $params = [];

    if (!empty($filters['search'])) {
      $where[] = 'd.Titre_pub LIKE :search';
      $params[':search'] = '%' . $filters['search'] . '%';
    }
    if (!empty($filters['category'])) {
      $where[] = 'd.Categorie = :category';
      $params[':category'] = (int) $filters['category'];
    }
    if (!empty($filters['direction'])) {
      $where[] = 'd.IDDirection = :direction';
      $params[':direction'] = (int) $filters['direction'];
    }
    if (!empty($filters['year'])) {
      $where[] = 'YEAR(d.Date_publication) = :year';
      $params[':year'] = (int) $filters['year'];
    }

    return [implode(' AND ', $where), $params];
  }

  /**
   * Returns the total count of publications matching the given filters.
   */
  public function countPublications(array $filters = []): int {
    [$where, $params] = $this->buildWhere($filters);
    try {
      return (int) $this->database->query(
        "SELECT COUNT(*) FROM tbl_documentation d WHERE $where",
        $params
      )->fetchField();
    }
    catch (\Exception $e) {
      \Drupal::logger('publications')->error('Count query failed: @msg', ['@msg' => $e->getMessage()]);
      return 0;
    }
  }

  /**
   * Gets paginated publications joined with category and direction.
   */
  public function getPublications(array $filters = [], int $limit = 20, int $offset = 0): array {
    [$where, $params] = $this->buildWhere($filters);
    $orderBy = $this->buildOrderBy($filters['sort'] ?? '');
    try {
      $rows = $this->database->query(
        "SELECT d.id, d.Titre_pub, d.Description, d.Date_publication,
                d.Nom_Document, d.Categorie, d.IDDirection,
                c.libelle AS category_label,
                dir.Direction AS direction_label
         FROM tbl_documentation d
         LEFT JOIN tbl_categorie_doc c ON d.Categorie = c.id
         LEFT JOIN tbl_direction dir ON d.IDDirection = dir.DirectionID
         WHERE $where
         ORDER BY $orderBy
         LIMIT $limit OFFSET $offset",
        $params
      )->fetchAll(\PDO::FETCH_ASSOC);

      $months = [
        1=>'janv.',2=>'févr.',3=>'mars',4=>'avr.',5=>'mai',6=>'juin',
        7=>'juil.',8=>'août',9=>'sept.',10=>'oct.',11=>'nov.',12=>'déc.',
      ];
      foreach ($rows as &$row) {
        $row['year'] = $row['Date_publication'] ? substr($row['Date_publication'], 0, 4) : '';
        if ($row['Date_publication']) {
          $d = new \DateTime($row['Date_publication']);
          $row['date_formatted'] = $d->format('d') . ' ' . $months[(int)$d->format('n')] . ' ' . $d->format('Y');
        } else {
          $row['date_formatted'] = '';
        }
      }

      return $rows ?: [];
    }
    catch (\Exception $e) {
      \Drupal::logger('publications')->error('DB query failed: @msg', ['@msg' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Gets all categories from tbl_categorie_doc.
   */
  public function getCategories(): array {
    try {
      return $this->database->query(
        "SELECT id, libelle AS name FROM tbl_categorie_doc ORDER BY libelle ASC"
      )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
    catch (\Exception $e) {
      \Drupal::logger('publications')->error('DB query failed: @msg', ['@msg' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Gets all directions from tbl_direction.
   */
  public function getDirections(): array {
    try {
      return $this->database->query(
        "SELECT DirectionID AS id, Direction AS name FROM tbl_direction ORDER BY Direction ASC"
      )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
    catch (\Exception $e) {
      \Drupal::logger('publications')->error('DB query failed: @msg', ['@msg' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Gets distinct years present in active publications.
   */
  public function getYears(): array {
    try {
      return $this->database->query(
        "SELECT DISTINCT YEAR(Date_publication) AS year
         FROM tbl_documentation
         WHERE Date_publication IS NOT NULL
         ORDER BY year DESC"
      )->fetchCol() ?: [];
    }
    catch (\Exception $e) {
      \Drupal::logger('publications')->error('DB query failed: @msg', ['@msg' => $e->getMessage()]);
      return [];
    }
  }

}
