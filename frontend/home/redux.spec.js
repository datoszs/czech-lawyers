import {List} from 'immutable';
import {TestingStore} from '../util';
import {courts} from '../model';
import {NAME} from './constants';
import reducer from './reducer';
import {setLeaderBoard} from './actions';
import {getBottom, getTop, getName} from './selectors';

describe('LeadearBoard module', () => {
    const advocate = (id, fullname, sortname) => ({id_advocate: id, fullname, sorting_name: sortname});

    const initial = new TestingStore(NAME, reducer);
    context('initial state', () => {
        it('there are no top advocates for any court', () => {
            Object.values(courts).forEach((court) => {
                initial.select(getBottom, court).should.equal(List());
            });
        });
        it('there are no bottom advocates for any court', () => {
            Object.values(courts).forEach((court) => {
                initial.select(getTop, court).should.equal(List());
            });
        });
    });
    context('when advocates are added', () => {
        const store = initial.apply(setLeaderBoard({
            [courts.NS]: [
                advocate(9342, 'JUDr. Ervin Perthen', 'Perthen, Ervin'),
                advocate(411, 'JUDr. Zbyněk Dvořák', 'Dvořák, Zbyněk'),
            ],
            [courts.NSS]: [
                advocate(690, 'Ing. Tomáš Matoušek', 'Matoušek, Tomáš'),
                advocate(8718, 'JUDr. Ing. Vladimír Nedvěd', 'Nedvěd, Vladimír'),
            ],
            [courts.US]: [
                advocate(2119, 'Prof. JUDr. Aleš Gerloch, CSc.', 'Gerloch, Aleš'),
                advocate(15613, 'JUDr. Šárka Toulová', 'Toulová, Šárka'),
            ],
        }, {
            [courts.NS]: [
                advocate(6991, 'Mgr. Lucie Brusová', 'Brusová, Lucie'),
                advocate(9290, 'JUDr. Petr Poledník', 'Poledník, Petr'),
            ],
            [courts.NSS]: [
                advocate(2628, 'JUDr. Milan Hulík, PhD.', 'Hulík, Marek'),
                advocate(305, 'JUDr. Anna Doležalová, PhD.', 'Doležalová, Anna'),
            ],
            [courts.US]: [
                advocate(2628, 'JUDr. Milan Hulík, PhD.', 'Hulík, Marek'),
                advocate(1554, 'Mgr. Jaroslav Čapek', 'Čapek, Jaroslav'),
            ],
        }));
        it('top advocates are sorted wrt "sorting name" for each court', () => {
            store.select(getTop, courts.NS).should.deep.equal(List.of(411, 9342));
            store.select(getTop, courts.NSS).should.deep.equal(List.of(690, 8718));
            store.select(getTop, courts.US).should.deep.equal(List.of(2119, 15613));
        });
        it('bottom advocates are sorted wrt "sorting name" for each court', () => {
            store.select(getBottom, courts.NS).should.deep.equal(List.of(6991, 9290));
            store.select(getBottom, courts.NSS).should.deep.equal(List.of(305, 2628));
            store.select(getBottom, courts.US).should.deep.equal(List.of(1554, 2628));
        });
        it('all advocate names are accesssible', () => {
            store.select(getName, 9342).should.equal('JUDr. Ervin Perthen');
            store.select(getName, 411).should.equal('JUDr. Zbyněk Dvořák');
            store.select(getName, 690).should.equal('Ing. Tomáš Matoušek');
            store.select(getName, 8718).should.equal('JUDr. Ing. Vladimír Nedvěd');
            store.select(getName, 2119).should.equal('Prof. JUDr. Aleš Gerloch, CSc.');
            store.select(getName, 15613).should.equal('JUDr. Šárka Toulová');
            store.select(getName, 6991).should.equal('Mgr. Lucie Brusová');
            store.select(getName, 9290).should.equal('JUDr. Petr Poledník');
            store.select(getName, 2628).should.equal('JUDr. Milan Hulík, PhD.');
            store.select(getName, 305).should.equal('JUDr. Anna Doležalová, PhD.');
            store.select(getName, 1554).should.equal('Mgr. Jaroslav Čapek');
        });
    });
});
