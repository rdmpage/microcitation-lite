# Microcitation-lite

SQLite database of publications, based on earlier MySQL version.


## SQLite

Experimenting with SQLite to keep things simple (e.g., no need to install database server).

## Tables

Have decided to have two tables, one for data derived from various sources, and one for data obtained by resolving DOIs. Might help in cases where there are multiple sources of journal data and none are complete (e.g., web pages may have more information than DOI, such as PDFs, other languages, etc.).

### Update timestamp

Need a trigger to have time stamps update when record added/modified:

```sql
-- DROP TRIGGER publications_updated;
CREATE TRIGGER publications_updated AFTER UPDATE ON publications FOR EACH ROW
BEGIN

UPDATE publications
SET
    updated = CURRENT_TIMESTAMP
WHERE guid = old.guid;

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



## Journals

### Korean Journal of Systematic Zoology

Journal has two titles, DOIs all have title set to Animal Systematics, Evolution And Diversity, metadata harvested from https://koreascience.kr/ and by resolving DOIs. Will need to split DOIs into two sets, add additional multilingual data, archive PDFs, and also handle articles pre-DOI. Wikidata has two versions of the Korean Journal of Systematic Zoology, likely based on ISSN issues.


