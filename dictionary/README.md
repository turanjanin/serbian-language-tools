# Dictionary of Serbian Words

This project features custom dictionary of Serbian words that are used for diacritic restoration and transliteration from Latin to Cyrillic scripts. Dictionary is distributed as a single SQLite database, located at `../resources/dictionary.sqlite`.

This dictionary is created using the following Unicode-encoded files:

`words.txt` - Set of words that contain at least one of the following characters: s, c, z, š, č, ć, ž, đ or one of the following digraphs: nj, lj, dj, dz. Each word is followed by a relative frequency of occurrence in a Serbian language.

`phrases.txt` - List of phrases that include words with diacritic characters, used for context disambiguation when dealing with multiple restoration candidates - e.g. `kuca` *(puppy)* vs `kuća` *(house)*.



## Extending the database

You can add additional entries to `words.txt` and `phrases.txt` files. After files are updated, SQLite database can be recreated by running the following script:

```bash
php build-database.php
```


## Acknowledgements

List of words used in this dictionary is assembled from various sources:

- [Serbian Hunspell spelling dictionary](https://github.com/grakic/hunspell-sr)
- [Jezička laboratorija](http://lab.unilib.rs/)
- [Serbian dictionary from LanguageTool project](https://github.com/languagetool-org/languagetool/tree/master/languagetool-language-modules/sr/src/main/resources/org/languagetool/resource/sr/dictionary)
- [List of words by user "reader" on mycity.rs forum](https://www.mycity.rs/Srpski-jezik/Provera-pravopisa-u-Libreoffice-u.html#p1937381)
- [Serbian Language Pipeline for Spacy](https://github.com/BCDH/spacy-serbian-pipeline)
- [Android LatinIME dictionaries](https://android.googlesource.com/platform/packages/inputmethods/LatinIME/+/master/dictionaries/)

Relative frequency of words is taken from the [srWaC - Serbian Web Corpus](https://www.clarin.si/noske/all.cgi/corp_info?corpname=srwac&struct_attr_stats=1&subcorpora=1).
