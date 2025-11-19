class ProfileDataManager {
    constructor() {
        this.defaultData = {
            username: 'username',
            fullname: 'fullname',
            professionalTitle: 'Full Stack Developer & UI/UX Enthusiast',
            location: 'Seattle, Washington',
            email: 'example@example.com',
            phone: '11111111',
            aboutMe: "I'm a passionate web developer focused on creating clean, user-friendly digital experiences. With a strong foundation in front-end technologies and a curiosity for continuous learning, I'm currently expanding my skills in React and UI/UX design. My goal is to build products that combine functionality with thoughtful design.",
            rating: 5,
            exchanges: 9,
            memberSince: 2025,
            profilePicture: '../../assets/images/Default_pfp.svg',
            offeringSkills: [
                { name: 'Web Development', proficiency: 92, rate: 50 },
                { name: 'React', proficiency: 88, rate: 50 },
                { name: 'Database', proficiency: 50, rate: 45 },
                { name: 'Node.js', proficiency: 76, rate: 50 },
                { name: 'UI/UX Design', proficiency: 80, rate: 50 }
            ],
            seekingSkills: ['Graphic Design', 'Logo Creation', 'Video Editing', 'Content Writing']
        };

        this.data = this.loadData();
    }

    loadData() {
        try {
            const stored = sessionStorage.getItem('profileData');
            if (stored) {
                console.log('Loading from sessionStorage');
                return JSON.parse(stored);
            }
        } catch (e) {
            console.log('sessionStorage not available');
        }
        
        if (window.profileData) {
            console.log('Loading from window.profileData');
            return window.profileData;
        }
        
        console.log('Using default data');
        window.profileData = { ...this.defaultData };
        return window.profileData;
    }

    saveData(newData) {
        console.log('Saving data:', newData);
        this.data = { ...this.data, ...newData };
        window.profileData = this.data;
        
        try {
            sessionStorage.setItem('profileData', JSON.stringify(this.data));
            console.log('Saved to sessionStorage');
        } catch (e) {
            console.log('sessionStorage not available');
        }
        
        console.log('Data after save:', this.data);
        return this.data;
    }

    getData() {
        return this.data;
    }

    updateField(field, value) {
        this.data[field] = value;
        window.profileData = this.data;
        return this.data;
    }

    addOfferingSkill(skill) {
        if (!this.data.offeringSkills.some(s => s.name === skill.name)) {
            this.data.offeringSkills.push(skill);
            window.profileData = this.data;
            return true;
        }
        return false;
    }

    removeOfferingSkill(skillName) {
        this.data.offeringSkills = this.data.offeringSkills.filter(s => s.name !== skillName);
        window.profileData = this.data;
    }

    updateOfferingSkill(skillName, updates) {
        const skillIndex = this.data.offeringSkills.findIndex(s => s.name === skillName);
        if (skillIndex !== -1) {
            this.data.offeringSkills[skillIndex] = {
                ...this.data.offeringSkills[skillIndex],
                ...updates
            };
            window.profileData = this.data;
        }
    }

    addSeekingSkill(skillName) {
        if (!this.data.seekingSkills.includes(skillName) && this.data.seekingSkills.length < 10) {
            this.data.seekingSkills.push(skillName);
            window.profileData = this.data;
            return true;
        }
        return false;
    }

    removeSeekingSkill(skillName) {
        this.data.seekingSkills = this.data.seekingSkills.filter(s => s !== skillName);
        window.profileData = this.data;
    }
}

window.profileManager = new ProfileDataManager();