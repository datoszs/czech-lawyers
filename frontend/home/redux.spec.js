import {List} from 'immutable';
import {TestingStore} from '../util';
import {NAME} from './constants';
import reducer from './reducer';
import {setLeaderBoard} from './actions';
import {getBottom, getTop, getName} from './selectors';

describe('LeadearBoard module', () => {
    const advocate = (id, fullname, sortname) => ({id_advocate: id, fullname, sorting_name: sortname});

    const initial = new TestingStore(NAME, reducer);
    context('initial state', () => {
        it('there are no top advocates', () => {
            initial.select(getBottom).should.equal(List());
        });
        it('there are no bottom advocates', () => {
            initial.select(getTop).should.equal(List());
        });
    });
    context('when advocates are added', () => {
        const store = initial.apply(setLeaderBoard([
            advocate(13913, 'JUDr. Alexander Klimeš', 'Klimeš, Alexander'),
            advocate(879, 'Mgr.et Mgr. Marek Čechovský', 'Čechovský Marek'),
            advocate(1847, 'Mgr. Dagmar Rezková Dřímalová', 'Rezková Dřímalová, Dagmar'),
        ], [
            advocate(12513, 'Mgr.et Mgr. Václav Sládek', 'Sládek, Václav'),
            advocate(121, 'JUDr. Tomáš Sokol', 'Sokol, Tomáš'),
            advocate(9857, 'Mgr. Ivana Sládková', 'Sládková, Ivana'),
        ]));
        it('top advocates are sorted wrt "sorting_name"', () => {
            store.select(getTop).should.deep.equal(List.of(879, 13913, 1847));
        });
        it('bottom advocates are sorted wrt "sorting_name"', () => {
            store.select(getBottom).should.deep.equal(List.of(12513, 9857, 121));
        });
        it('all advocate names are accessible', () => {
            store.select(getName, 13913).should.equal('JUDr. Alexander Klimeš');
            store.select(getName, 879).should.equal('Mgr.et Mgr. Marek Čechovský');
            store.select(getName, 1847).should.equal('Mgr. Dagmar Rezková Dřímalová');
            store.select(getName, 12513).should.equal('Mgr.et Mgr. Václav Sládek');
            store.select(getName, 121).should.equal('JUDr. Tomáš Sokol');
            store.select(getName, 9857).should.equal('Mgr. Ivana Sládková');
        });
    });
});
