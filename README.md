# Microcitation-lite

SQLite database of publications, based on earlier MySQL version.


## SQLite

Experimenting with SQLite to keep things simple (e.g., no need to install database server.

### Update timestamp

Need a trigger to have time stamps update when record added/modified:

```sql
-- DROP TRIGGER publications_updated;
CREATE TRIGGER publications_updated AFTER UPDATE ON publications FOR EACH ROW
BEGIN

UPDATE publications
SET
    updated = CURRENT_TIMESTAMP
WHERE id = old.id;

END;

```

### Views

```sql
CREATE VIEW IF NOT EXISTS `Korean_journal_of_applied_entomology` AS
SELECT 
    guid, 
    title, 
    journal,
    issn,
    volume,
    issue,
    spage,
    epage,
    year,
    doi
FROM `publications` 
WHERE `publications`.journal="Korean journal of applied entomology";
```




