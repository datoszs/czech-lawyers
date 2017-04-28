import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {Panel} from 'react-bootstrap';
import {TwoColumn} from '../components';
import autocomplete from '../autocomplete';

import AdvocateDetail from './AdvocateDetail';
import {search} from './modules';

const Container = ({advocates}) => (
    <section>
        <Panel>
            <autocomplete.Container />
        </Panel>
        <TwoColumn>
            {advocates.map((id) => <AdvocateDetail key={id} id={id} />)}
        </TwoColumn>
    </section>
);

Container.propTypes = {
    advocates: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    advocates: search.getIds(state),
});

export default connect(mapStateToProps)(Container);
