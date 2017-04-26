import React from 'react';
import PropTypes from 'prop-types';
import {compose} from 'redux';
import {connect} from 'react-redux';
import {ButtonGroup, Button} from 'react-bootstrap';
import {courts, courtsMsg} from '../model';
import {Msg} from '../containers';
import {getCourtFilter} from './selectors';
import {setCourtFilter} from './actions';

const CourtFilterComponent = ({activeCourt, setActiveCourt}) => (
    <ButtonGroup>
        <Button
            key="no-court"
            onClick={() => (activeCourt !== null ? setActiveCourt(null) : {})}
            active={activeCourt === null}
        >
            <Msg msg="court.all" />
        </Button>
        {Object.values(courts).map((court) =>
            <Button
                key={court}
                onClick={() => (court !== activeCourt ? setActiveCourt(court) : {})}
                active={court === activeCourt}
            >
                <Msg msg={courtsMsg[court]} />
            </Button>,
        )}
    </ButtonGroup>
);

CourtFilterComponent.propTypes = {
    activeCourt: PropTypes.oneOf(Object.values(courts)),
    setActiveCourt: PropTypes.func.isRequired,
};

CourtFilterComponent.defaultProps = {
    activeCourt: null,
};

const mapStateToProps = (state) => ({
    activeCourt: getCourtFilter(state),
});

const mapDispatchToProps = (dispatch) => ({
    setActiveCourt: compose(dispatch, setCourtFilter),
});

export default connect(mapStateToProps, mapDispatchToProps)(CourtFilterComponent);
