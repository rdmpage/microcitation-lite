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

## Get PDFs

```sql
SELECT """" || pdf || ",""" FROM publications WHERE issn='1123-6787' AND pdf IS NOT NULL;
```

## Wikidata

### Add DOIs

```sql
SELECT wikidata, "P356", """" || UPPER(doi) || """" , "P2378" , "Q5188229" FROM publications_doi WHERE issn='0459-8113' and updated > '2022-12-29 14:00:00' AND wikidata IS NOT NULL AND doi IS NOT NULL;
```

### Add CNKI

```sql
SELECT wikidata, "P6769", """" || cnki || """"  FROM publications_doi WHERE issn='2095-0357' and updated > '2022-12-29 14:00:00' AND wikidata IS NOT NULL AND cnki IS NOT NULL;
```


### Adding PDF with wayback archive URL

Add qualifiers that say it’s a PDF and it’s backed up.

```sql
SELECT wikidata, "P953", """" || pdf || """",  "P2701", "Q42332", "P1065", """" || "https://web.archive.org" || waybackmachine || """" FROM publications WHERE issn='1225-0104' AND wikidata IS NOT NULL AND pdf IS NOT NULL AND waybackmachine IS NOT NULL;
```

### Internet Archive

```sql
SELECT wikidata, "P724", """" || internetarchive || """" FROM publications WHERE doi LIKE "10.5635/ASED%" AND wikidata IS NOT NULL AND internetarchive IS NOT NULL;
```
### Add volume and issue

```sql
SELECT wikidata, "P478", """" || volume || """" FROM publications WHERE issn="2346-9641" AND wikidata IS NOT NULL AND volume IS NOT NULL;
```

```sql
SELECT wikidata, "P433", """" || issue || """" FROM publications WHERE issn="2346-9641" AND wikidata IS NOT NULL AND issue IS NOT NULL;
```


### Add multilingual titles and labels

#### Titles

```
SELECT wikidata, "P1476", language || ":" || """" || value || """", "S248", "Q4698727", "S854", """" || guid || """"
FROM publications 
INNER JOIN multilingual USING(guid) 
WHERE issn='1021-5506'
AND year < 2013
AND multilingual.`language` = 'zh'
AND multilingual.`key` = 'title'
AND wikidata IS NOT NULL;
```

#### Labels

```
SELECT wikidata, "L" || language, """" || value || """"
FROM publications 
INNER JOIN multilingual USING(guid) 
WHERE issn='1021-5506'
AND year < 2013
AND multilingual.`language` = 'zh'
AND multilingual.`key` = 'title'
AND wikidata IS NOT NULL;
```



### Translations

```
SELECT wikidata_en, "P629", wikidata_ru FROM trans WHERE wikidata_en IS NOT NULL and wikidata_ru IS NOT NULL and en IS NULL;
```