-- Création du schéma et sélection du schéma
CREATE SCHEMA IF NOT EXISTS AnalyseGeo;

--------------------------------------------------
-- Table import_villes (temporaire)
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo.import_villes (
    city         VARCHAR(50),
    city_ascii   VARCHAR(50),
    lat          FLOAT,
    lng          FLOAT,
    country      VARCHAR(50),
    iso2         CHAR(2),
    iso3         CHAR(3),
    admin_name   VARCHAR(255),
    capital      VARCHAR(50),
    population   VARCHAR(255),
    id           INTEGER PRIMARY KEY
);
-- DROP TABLE AnalyseGeo.import_villes;

--------------------------------------------------
-- Table _villes
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo._villes (
    id         INTEGER NOT NULL PRIMARY KEY,
    nom_ville  VARCHAR(50) NOT NULL,
    latitude   FLOAT NOT NULL,
    longitude  FLOAT NOT NULL,
    iso        CHAR(4) NOT NULL,
    nom_pays   VARCHAR(50) NOT NULL
);
-- DROP TABLE AnalyseGeo._villes;

-- Créer une séquence si elle n'existe pas déjà
CREATE SEQUENCE IF NOT EXISTS AnalyseGeo._villes_id_seq;

-- Mettre à jour la séquence avec la valeur maximale existante
SELECT setval('AnalyseGeo._villes_id_seq', (SELECT MAX(id) FROM AnalyseGeo._villes));

-- Modifier la colonne "id" pour utiliser la séquence
ALTER TABLE AnalyseGeo._villes
ALTER COLUMN id SET DEFAULT nextval('AnalyseGeo._villes_id_seq');

--------------------------------------------------
-- Table pays_continent
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo.pays_continent (
  continent  VARCHAR(15),
  pays       VARCHAR(50)
);
-- DROP TABLE AnalyseGeo.pays_continent;

--------------------------------------------------
-- Chargement et insertion des données
--------------------------------------------------
COPY AnalyseGeo.import_villes
FROM '/docker-entrypoint-initdb.d/worldcities.csv'
WITH (FORMAT csv, HEADER true, DELIMITER ',');

INSERT INTO AnalyseGeo._villes (id, nom_ville, latitude, longitude, iso, nom_pays)
SELECT DISTINCT id, city_ascii, lat, lng, iso3, country
FROM AnalyseGeo.import_villes
ON CONFLICT (id) DO NOTHING;

COPY AnalyseGeo.pays_continent (continent, pays)
FROM '/docker-entrypoint-initdb.d/Countries-Continents.csv'
WITH (DELIMITER ',', FORMAT csv, HEADER true);

DROP TABLE AnalyseGeo.import_villes;

--------------------------------------------------
-- Vue pour toutes les infos géographiques
--------------------------------------------------
CREATE OR REPLACE VIEW AnalyseGeo.touteslesinfos AS (
  SELECT * FROM AnalyseGeo._villes
  INNER JOIN AnalyseGeo.pays_continent ON pays = nom_pays
);

--------------------------------------------------
-- Table _publications
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo._publications (
  id_dblp  VARCHAR(255),
  type     VARCHAR(255),
  doi      VARCHAR(255),
  titre    VARCHAR(255),
  lieu     VARCHAR(255),
  annee    INTEGER,
  pages    VARCHAR(50),
  ee       VARCHAR(255),
  url_dblp VARCHAR(255)
);
ALTER TABLE AnalyseGeo._publications ADD PRIMARY KEY (id_dblp);
-- DROP TABLE AnalyseGeo._publications;

--------------------------------------------------
-- Table _revues (héritée de _publications)
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo._revues (
  volume  VARCHAR(255),
  numero  INTEGER
) INHERITS (AnalyseGeo._publications);
ALTER TABLE AnalyseGeo._revues ADD PRIMARY KEY (id_dblp);
-- DROP TABLE AnalyseGeo._revues;

--------------------------------------------------
-- Table _conferences (héritée de _publications)
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo._conferences (
  -- données à intégrer ultérieurement
) INHERITS (AnalyseGeo._publications);
ALTER TABLE AnalyseGeo._conferences ADD PRIMARY KEY (id_dblp);
-- DROP TABLE AnalyseGeo._conferences;

--------------------------------------------------
-- Table _auteurs
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo._auteurs (
  pid     VARCHAR(255),
  orc_id  VARCHAR(255),
  nom     VARCHAR(255)
);
ALTER TABLE AnalyseGeo._auteurs ADD PRIMARY KEY (pid);
-- DROP TABLE AnalyseGeo._auteurs;

--------------------------------------------------
-- Table a_ecrit : relation entre publications et auteurs
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo.a_ecrit (
  id_dblp  VARCHAR(255),
  pid      VARCHAR(255),
  ordre    INT
);
ALTER TABLE AnalyseGeo.a_ecrit ADD PRIMARY KEY (id_dblp, pid);
ALTER TABLE AnalyseGeo.a_ecrit ADD FOREIGN KEY (id_dblp) REFERENCES AnalyseGeo._publications(id_dblp);
ALTER TABLE AnalyseGeo.a_ecrit ADD FOREIGN KEY (pid) REFERENCES AnalyseGeo._auteurs(pid);
-- DROP TABLE AnalyseGeo.a_ecrit;

--------------------------------------------------
-- Table _structures
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo._structures (
  id_struct   VARCHAR(50),
  ror         VARCHAR(255),
  acronyme    VARCHAR(50),
  nom_struct  VARCHAR(255),
  id_ville    INTEGER
);
ALTER TABLE AnalyseGeo._structures ADD PRIMARY KEY (id_struct);
ALTER TABLE AnalyseGeo._structures ADD CONSTRAINT fk_ville FOREIGN KEY (id_ville) REFERENCES AnalyseGeo._villes(id);
-- DROP TABLE AnalyseGeo._structures;

--------------------------------------------------
-- Table _affiliation : relation entre auteur et structure
--------------------------------------------------
CREATE TABLE IF NOT EXISTS AnalyseGeo._affiliation (
    pid       VARCHAR(255),
    id_struct VARCHAR(50)  
);
ALTER TABLE AnalyseGeo._affiliation ADD PRIMARY KEY (pid, id_struct);
ALTER TABLE AnalyseGeo._affiliation ADD FOREIGN KEY (pid) REFERENCES AnalyseGeo._auteurs(pid) ON DELETE CASCADE;
ALTER TABLE AnalyseGeo._affiliation ADD FOREIGN KEY (id_struct) REFERENCES AnalyseGeo._structures(id_struct) ON DELETE CASCADE;
-- DROP TABLE AnalyseGeo._affiliation;