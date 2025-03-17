create schema tp1;
set schema 'tp1';
drop table pays_continent;

create table if not exists pays_continent(
  continent varchar(15),
  pays varchar(50)
  );
  
\copy pays_continent (continent, pays)
    from './Countries-Continents.csv' WITH DELIMITER ',' CSV HEADER;
    

create or replace view touteslesinfo as (
  select * from temp_ville inner join pays_continent on pays = country);  

select count(city) as nb_ville, pays from touteslesinfo group by (pays) order by pays ASC; 
select count(pays) as nb_pays, continent from pays_continent group by (continent) order by continent ASC; 
select count(city) as nb_ville, continent from touteslesinfo group by (continent) order by continent ASC;
select max(city) as max_ville, pays from touteslesinfo group by (pays) order by pays ASC; 

