import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {Panel} from 'react-bootstrap';
import {TwoColumn} from '../components';
import {Msg} from '../containers';
import autocomplete from '../autocomplete';
import {SearchStatus, PageTitle} from '../containers/search';

import AdvocateDetail from './AdvocateDetail';
import CurrentSearch from './CurrentSearchContainer';
import SearchDisclaimer from './SearchDisclaimerContainer';
import {search} from './modules';

const Container = ({advocates}) => (
    <section>
        <PageTitle msg="advocate.search.title" module={search} />
        <header><h1><Msg msg="advocate.search.title" /></h1></header>
        <Panel>
            <autocomplete.Container />
        </Panel>
        <CurrentSearch />
        <SearchDisclaimer />
        <TwoColumn>
            {advocates.map((id) => <AdvocateDetail key={id} id={id} />)}
        </TwoColumn>
        <SearchStatus module={search} />
    </section>
);

Container.propTypes = {
    advocates: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    advocates: search.getIds(state),
});

export default connect(mapStateToProps)(Container);
