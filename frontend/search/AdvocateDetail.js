import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Row} from 'react-bootstrap';
import translate from '../translate';
import {Statistics, DetailPanel} from '../components';
import {Advocate, statusMsg} from '../model';
import {getAdvocate} from './selectors';
import FooterColumn from './FooterColumn';

const AdvocateDetailComponent = ({advocate, msgStatus, msgIc}) => (
    <DetailPanel
        footer={
            <Row>
                <FooterColumn value={advocate.address.city} />
                <FooterColumn value={advocate.ic} label={msgIc} />
                <FooterColumn value={msgStatus} />
            </Row>
        }
        title={advocate.name}
    >
        <Statistics
            positive={advocate.statistics.positive}
            negative={advocate.statistics.negative}
            neutral={advocate.statistics.neutral}
        />
    </DetailPanel>
);

AdvocateDetailComponent.propTypes = {
    advocate: PropTypes.instanceOf(Advocate).isRequired,
    msgStatus: PropTypes.string.isRequired,
    msgIc: PropTypes.string.isRequired,
};

const mapStateToProps = (state, {id}) => {
    const advocate = getAdvocate(state, id);
    return {
        advocate,
        msgStatus: translate.getMessage(state, statusMsg[advocate.status]),
        msgIc: translate.getMessage(state, 'advocate.ic'),
    };
};

const AdvocateDetail = connect(mapStateToProps)(AdvocateDetailComponent);

AdvocateDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default AdvocateDetail;
