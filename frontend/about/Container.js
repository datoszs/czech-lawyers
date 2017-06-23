import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import {Statistics} from '../model';
import {BasicStatistics, CourtStatistics, BigStatistics} from '../components/statistics';

const statistics = (positive, negative, neutral) => new Statistics({positive, negative, neutral});

const Container = () => (
    <section>
        <BasicStatistics statistics={statistics(85, 315, 8)} />
        <CourtStatistics
            court="Nejvyšší soud"
            statistics={statistics(46, 86, 3)}
            courtStatistics={statistics(12702, 3494, 1037)}
        />
        <CourtStatistics
            court="Ústavní soud"
            statistics={statistics(19, 229, 5)}
            courtStatistics={statistics(3005, 36548, 672)}
        />
        <BigStatistics
            statistics={statistics(85, 315, 8)}
            msg={statistics('meritorních konečných rozhodnutí', 'nemeritorních konečných rozhodnutí', 'rozhodnutí o zastavení řízení')}
        />

        <PageHeader><Msg msg="about.title" /></PageHeader>
        <RichText msg="about.text" />
    </section>
);

export default Container;
