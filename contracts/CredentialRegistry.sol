// SPDX-License-Identifier: MIT
pragma solidity ^0.8.19;

/**
 * @title CredentialRegistry
 * @dev Smart contract for storing and managing verifiable credential hashes
 * @author SecureVerify Team
 */
contract CredentialRegistry {
    
    // Events
    event CredentialStored(
        string indexed credentialHash,
        string indexed vcId,
        string indexed issuerDid,
        uint256 timestamp,
        uint256 blockNumber
    );
    
    event CredentialRevoked(
        string indexed credentialHash,
        string reason,
        uint256 timestamp,
        uint256 blockNumber
    );
    
    event IssuerRegistered(
        string indexed issuerDid,
        address indexed issuerAddress,
        string name,
        string organizationType,
        uint256 timestamp
    );
    
    // Structs
    struct Credential {
        string credentialHash;
        string vcId;
        string issuerDid;
        uint256 timestamp;
        uint256 blockNumber;
        bool isRevoked;
        string revocationReason;
        uint256 revokedAt;
    }
    
    struct Issuer {
        string did;
        address issuerAddress;
        string name;
        string organizationType;
        bool isActive;
        uint256 registeredAt;
        uint256 credentialsIssued;
    }
    
    // State variables
    mapping(string => Credential) public credentials;
    mapping(string => Issuer) public issuers;
    mapping(address => string) public addressToDid;
    mapping(string => bool) public credentialExists;
    
    address public owner;
    uint256 public totalCredentials;
    uint256 public totalIssuers;
    
    // Modifiers
    modifier onlyOwner() {
        require(msg.sender == owner, "Only owner can call this function");
        _;
    }
    
    modifier onlyRegisteredIssuer() {
        require(bytes(addressToDid[msg.sender]).length > 0, "Only registered issuers can call this function");
        require(issuers[addressToDid[msg.sender]].isActive, "Issuer is not active");
        _;
    }
    
    modifier credentialNotExists(string memory _credentialHash) {
        require(!credentialExists[_credentialHash], "Credential already exists");
        _;
    }
    
    modifier credentialMustExist(string memory _credentialHash) {
        require(credentialExists[_credentialHash], "Credential does not exist");
        _;
    }
    
    // Constructor
    constructor() {
        owner = msg.sender;
        totalCredentials = 0;
        totalIssuers = 0;
    }
    
    /**
     * @dev Register a new issuer organization
     * @param _did The issuer's DID
     * @param _name The issuer's name
     * @param _organizationType The type of organization
     */
    function registerIssuer(
        string memory _did,
        string memory _name,
        string memory _organizationType
    ) external {
        require(bytes(_did).length > 0, "DID cannot be empty");
        require(bytes(_name).length > 0, "Name cannot be empty");
        require(bytes(addressToDid[msg.sender]).length == 0, "Address already registered");
        require(!issuers[_did].isActive, "DID already registered");
        
        issuers[_did] = Issuer({
            did: _did,
            issuerAddress: msg.sender,
            name: _name,
            organizationType: _organizationType,
            isActive: true,
            registeredAt: block.timestamp,
            credentialsIssued: 0
        });
        
        addressToDid[msg.sender] = _did;
        totalIssuers++;
        
        emit IssuerRegistered(_did, msg.sender, _name, _organizationType, block.timestamp);
    }
    
    /**
     * @dev Store a new credential hash
     * @param _credentialHash The SHA-256 hash of the credential
     * @param _vcId The verifiable credential ID
     * @param _issuerDid The issuer's DID
     */
    function storeCredential(
        string memory _credentialHash,
        string memory _vcId,
        string memory _issuerDid
    ) external onlyRegisteredIssuer credentialNotExists(_credentialHash) {
        require(bytes(_credentialHash).length > 0, "Credential hash cannot be empty");
        require(bytes(_vcId).length > 0, "VC ID cannot be empty");
        require(keccak256(abi.encodePacked(_issuerDid)) == keccak256(abi.encodePacked(addressToDid[msg.sender])), "Issuer DID mismatch");
        
        credentials[_credentialHash] = Credential({
            credentialHash: _credentialHash,
            vcId: _vcId,
            issuerDid: _issuerDid,
            timestamp: block.timestamp,
            blockNumber: block.number,
            isRevoked: false,
            revocationReason: "",
            revokedAt: 0
        });
        
        credentialExists[_credentialHash] = true;
        issuers[_issuerDid].credentialsIssued++;
        totalCredentials++;
        
        emit CredentialStored(_credentialHash, _vcId, _issuerDid, block.timestamp, block.number);
    }
    
    /**
     * @dev Revoke a credential
     * @param _credentialHash The credential hash to revoke
     * @param _reason The reason for revocation
     */
    function revokeCredential(
        string memory _credentialHash,
        string memory _reason
    ) external onlyRegisteredIssuer credentialMustExist(_credentialHash) {
        Credential storage credential = credentials[_credentialHash];
        require(keccak256(abi.encodePacked(credential.issuerDid)) == keccak256(abi.encodePacked(addressToDid[msg.sender])), "Only issuer can revoke");
        require(!credential.isRevoked, "Credential already revoked");
        
        credential.isRevoked = true;
        credential.revocationReason = _reason;
        credential.revokedAt = block.timestamp;
        
        emit CredentialRevoked(_credentialHash, _reason, block.timestamp, block.number);
    }
    
    /**
     * @dev Verify if a credential exists and is valid
     * @param _credentialHash The credential hash to verify
     * @return exists Whether the credential exists
     * @return issuerDid The issuer's DID
     * @return timestamp When the credential was stored
     * @return blockNumber The block number when stored
     * @return isRevoked Whether the credential is revoked
     */
    function verifyCredential(string memory _credentialHash) external view returns (
        bool exists,
        string memory issuerDid,
        uint256 timestamp,
        uint256 blockNumber,
        bool isRevoked
    ) {
        if (!credentialExists[_credentialHash]) {
            return (false, "", 0, 0, false);
        }
        
        Credential memory credential = credentials[_credentialHash];
        return (
            true,
            credential.issuerDid,
            credential.timestamp,
            credential.blockNumber,
            credential.isRevoked
        );
    }
    
    /**
     * @dev Get credential details
     * @param _credentialHash The credential hash
     * @return credential The credential struct
     */
    function getCredential(string memory _credentialHash) external view credentialMustExist(_credentialHash) returns (Credential memory) {
        return credentials[_credentialHash];
    }
    
    /**
     * @dev Get issuer details
     * @param _issuerDid The issuer's DID
     * @return issuer The issuer struct
     */
    function getIssuer(string memory _issuerDid) external view returns (Issuer memory) {
        return issuers[_issuerDid];
    }
    
    /**
     * @dev Get issuer DID from address
     * @param _address The issuer's address
     * @return did The issuer's DID
     */
    function getIssuerDid(address _address) external view returns (string memory) {
        return addressToDid[_address];
    }
    
    /**
     * @dev Check if an address is a registered issuer
     * @param _address The address to check
     * @return isRegistered Whether the address is registered
     */
    function isRegisteredIssuer(address _address) external view returns (bool) {
        string memory did = addressToDid[_address];
        return bytes(did).length > 0 && issuers[did].isActive;
    }
    
    /**
     * @dev Deactivate an issuer (only owner)
     * @param _issuerDid The issuer's DID to deactivate
     */
    function deactivateIssuer(string memory _issuerDid) external onlyOwner {
        require(issuers[_issuerDid].isActive, "Issuer is not active");
        issuers[_issuerDid].isActive = false;
    }
    
    /**
     * @dev Reactivate an issuer (only owner)
     * @param _issuerDid The issuer's DID to reactivate
     */
    function reactivateIssuer(string memory _issuerDid) external onlyOwner {
        require(!issuers[_issuerDid].isActive, "Issuer is already active");
        require(bytes(issuers[_issuerDid].did).length > 0, "Issuer does not exist");
        issuers[_issuerDid].isActive = true;
    }
    
    /**
     * @dev Transfer ownership (only owner)
     * @param _newOwner The new owner address
     */
    function transferOwnership(address _newOwner) external onlyOwner {
        require(_newOwner != address(0), "New owner cannot be zero address");
        owner = _newOwner;
    }
    
    /**
     * @dev Get contract statistics
     * @return _totalCredentials Total credentials stored
     * @return _totalIssuers Total registered issuers
     * @return _owner Contract owner address
     */
    function getStats() external view returns (
        uint256 _totalCredentials,
        uint256 _totalIssuers,
        address _owner
    ) {
        return (totalCredentials, totalIssuers, owner);
    }
    
    /**
     * @dev Check if credential is valid (exists and not revoked)
     * @param _credentialHash The credential hash to check
     * @return isValid Whether the credential is valid
     */
    function isValidCredential(string memory _credentialHash) external view returns (bool) {
        return credentialExists[_credentialHash] && !credentials[_credentialHash].isRevoked;
    }
    
    /**
     * @dev Get credentials count for an issuer
     * @param _issuerDid The issuer's DID
     * @return count Number of credentials issued
     */
    function getIssuerCredentialsCount(string memory _issuerDid) external view returns (uint256) {
        return issuers[_issuerDid].credentialsIssued;
    }
} 