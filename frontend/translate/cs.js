import about from './about.cs.md';
import home from './home.cs.md';
import authors from './authors.cs.md';
import contact from './contact.cs.md';
import dispute from './dispute.cs.md';
import advocateCasesDisclaimer from './advocate.cases.disclaimer.cs.md';

/* eslint-disable quote-props */
export default {
    'app.title': 'Čeští advokáti.cz',
    'about.title': 'O projektu',
    'contact.title': 'Kontakt',
    'contact.appeal': 'Napište nám',
    'copyright': '\u{A9} 2017 DATOS \u{2014} data o spravedlnosti, z. s.',
    'loading.alert': 'Váš požadavek se zpracovává, počkejte, prosím.',

    'search.button': 'Hledat',
    'search.placeholder': 'Zadejte jméno nebo IČ',
    'back.button': 'Zpět',
    'search.results': 'Zobrazeny výsledky pro:',

    'advocates.title': 'Advokáti',
    'advocate.search.title': 'Vyhledávání advokátů',

    'advocate.ic': 'IČ',
    'advocate.registration.number': 'Evidenční číslo',
    'advocate.address': 'Sídlo',
    'advocate.status': 'Stav',
    'advocate.email': 'E-mail',
    'advocate.cases': 'Případy',
    'advocate.same.name.warning': 'Tento advokát má jméno i příjmení shodné s alespoň jedním dalším advokátem. Jeho statistiky tedy mohou být nepřesné ([přečtěte si více](/about)). Stejnojmenní advokáti:',

    'status.active': 'Aktivní',
    'status.suspended': 'Pozastavený',
    'status.removed': 'Neaktivní', // Originally: Vyškrtnutý

    'advocate.detail.title': 'Detail advokáta',
    'cak.link': 'Detail na stránkách České advokátní komory',
    'advocate.cases.disclaimer': advocateCasesDisclaimer,

    'stats.positive.legend': 'meritorních konečných rozhodnutí',
    'stats.negative.legend': 'nemeritorních konečných rozhodnutí',
    'stats.neutral.legend': 'rozhodnutí o\u{000A0}zastavení řízení',

    'court.ns': 'Nejvyšší soud',
    'court.nss': 'Nejvyšší správní soud',
    'court.us': 'Ústavní soud',
    'court.all': 'Všechny soudy',

    'result.positive': 'Meritorní',
    'result.negative': 'Nemeritorní',
    'result.neutral': 'Zastavení řízení',

    'cases.title': 'Případy',
    'case.search.title': 'Vyhledávání případů',
    'cases.search.placeholder': 'Zadejte spisovou značku',

    'case.detail.title': 'Detail případu',
    'case.advocate': 'Advokát',
    'case.court': 'Soud',
    'case.result': 'Výsledek',
    'case.documents': 'Dokumenty',
    'case.documents.empty': 'K tomuto případu nemáme žádné dokumenty.',
    'case.dispute.text': dispute,
    'case.dispute.submit': 'Rozporovat',
    'case.dispute': 'Rozporovat výsledek',
    'case.dispute.reason': 'Důvod k rozporování',
    'case.dispute.reason.result': 'Špatně přiřazený výsledek',
    'case.dispute.reason.advocate': 'Špatně přiřazený advokát',
    'case.dispute.reason.both': 'Špatně přiřazený advokát i výsledek',
    'case.dispute.comment': 'Vysvětlení',
    'case.dispute.success': 'Váš požadavek byl zaznamenán. Během chvíle by Vám měl přijít e-mail s potvrzovacím odkazem.',
    'case.dispute.error.default': 'Případ se bohužel nepodařilo rozporovat. Zkuste to znova, možná budete mít více štěstí.',
    'case.dispute.error.inconsistent': 'Vypadá to, že rozporujete zastaralá data. Obnovte, prosím, stránku a zkuste to znova.',

    'case.dispute.final.advocate': 'Advokáta tomuto případu přiřadil živý člověk a jsme si jím poměrně jistí. Pokud přesto nesouhlasíte s naším úsudkem, neváhejte a [kontaktujte nás](/contact#form.)',
    'case.dispute.final.result': 'Výsledek tomuto případu přiřadil živý člověk a jsme si jím poměrně jistí. Pokud přesto nesouhlasíte s naším úsudkem, neváhejte a [kontaktujte nás](/contact#form.)',
    'case.dispute.final.both': 'Advokáta i výsledek tomuto případu přiřadil živý člověk a jsme si jimi poměrně jistí. Pokud přesto nesouhlasíte s naším úsudkem, neváhejte a [kontaktujte nás](/contact#form.)',

    'case.dispute.verify.title': 'Rozporování případu',
    'case.dispute.verify.case': 'Přejít na stránku případu',
    'case.dispute.verify.success': 'Případ byl úspěšně rozporován.',
    'case.dispute.verify.fail': 'Z nám neznámého důvodu se případ nepodařilo rozporovat. Zkuste to znova, možná budete mít více štěstí.',
    'case.dispute.verify.no.request': 'Váš požadavek na rozporování se nám někde ztratl. Rozporujte, prosím, případ, znova.',
    'case.dispute.verify.inconsistent': 'Někdo byl rychlejší než Vy a případ dříve, než byl rozporován, změnil. Podívejte se, prosím, na stránku případu a ověřte přiřazeného advokáta a/nebo výsledek.',

    'home.above': 'Zadejte advokáta a zobrazte si statistiky o jeho vystupování před třemi nejdůležitějšími soudy v ČR.',
    'home.below': home,
    'home.cak.search': 'Pokud advokáta, jehož statistiky byste si chtěli zobrazit, teprve hledáte, využijte možnost [vyhledávání advokátů podle zaměření nebo sídla](http://vyhledavac.cak.cz) na stránkách České advokátní komory.',
    'leaderboard.legend': 'Spočítali jsme pro Vás, kterých deset advokátů má největší převahu **+meritorních+** konečných rozhodnutí nad **-nemeritorními-** a naopak ([proč? co to znamená?](/about)). Upozorňujeme ovšem, že data, která jsme pro výpočet použili, [nemusí být úplná](/about).',
    'home.disclaimer': 'Účelem tohoto webu není poskytovat právní služby, ani jejich poskytování zprostředkovávat. Web zpracovává data veřejných institucí. Za věcnou správnost těchto dat autoři webu nenesou odpovědnost. Způsob využití zde zveřejňovaných statistik je zcela v rukou uživatelů, autoři webu za něj nenesou odpovědnost',

    'about.text': about,

    'contact.subtitle': 'Za projektem stojí:',
    'contact.authors': authors,
    'contact.us.text': contact,
    'contact.form.message': 'Vaše zpráva',
    'contact.form.submit': 'Odeslat',
    'contact.form.success': 'Vaše zpráva byla odeslána. Děkujeme Vám za Váš názor.',
    'contact.form.error.default': 'Vaši zprávu se bohužel nepodařilo odeslat. Zkuste to znova, možná budete mít více štěstí.',

    'society.name': 'DATOS \u{2014} data o spravedlnosti z. s.',
    'society.ic': 'IČ: 05003997',
    'society.street': 'Fleischnerova 20',
    'society.city': '635\u{000A0}00 Brno',

    'form.email': 'Váš e-mail',
    'form.name': 'Vaše jméno',
    'form.error.required': 'Toto pole je povinné',
    'form.error.email': 'Toto pole musí obsahovat validní e-mailovou adresu',
};
